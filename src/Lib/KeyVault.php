<?php


namespace Rudl\Vault\Lib;


use Phore\Core\Exception\InvalidDataException;
use Phore\Core\Exception\NotFoundException;
use Phore\Core\Helper\PhoreSecretBoxAsync;
use Phore\Core\Helper\PhoreSecretBoxSync;
use Rudl\Vault\Lib\Ex\KeyNotUnlockedException;
use Rudl\Vault\Lib\Type\T_KeyPair;
use Rudl\Vault\Lib\Type\T_Secret;

class KeyVault
{

    public $unlockedKeys = [];

    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function createKeyPair (string $kid, string $secret)
    {
        $keyPair = T_KeyPair::maintain($this->config->config->keypairs, ["key_id" => $kid], true);
        if ($keyPair->private_key_enc !== null)
            throw new InvalidDataException("Keypair key_id '$kid' already existing.");

        $asyncBox = new PhoreSecretBoxAsync();
        $newKeyPair = $asyncBox->createKeyPair();

        $syncBox = new PhoreSecretBoxSync($secret);

        $keyPair->public_key = $newKeyPair["public_key"];
        $keyPair->private_key_enc = $syncBox->encrypt($newKeyPair["private_key"]);
        $this->config->save();
    }

    public function defaultKeyPairId() : ?string
    {
        if (count ($this->config->config->keypairs) === 1)
            return $this->config->config->keypairs[0]->key_id;
        return null;
    }

    public function encrypt(string $unencryptedContent, string $kid)
    {
        $keyPair = T_KeyPair::maintain($this->config->config->keypairs, ["key_id" => $kid], true);
        if ($keyPair->private_key_enc === null)
            throw new InvalidDataException("Keypair key_id '$kid' not existing.");
        $async = new PhoreSecretBoxAsync();
        return $async->encrypt($unencryptedContent, $keyPair->public_key);
    }

    public function decrypt(string $encryptedContent, string $kid) : string
    {
        $keyPair = T_KeyPair::maintain($this->unlockedKeys, ["key_id" => $kid], false, new KeyNotUnlockedException($kid));
        $async = new PhoreSecretBoxAsync();
        return $async->decrypt($encryptedContent, $keyPair->private_key_enc);
    }

    public function createSecret (string $name, string $unencryptedContent, string $kid)
    {
        $secret = T_Secret::maintain($this->config->config->secrets, ["name" => $name], true);
        if ($secret->secret_val_enc !== null)
            throw new InvalidDataException("Secret with name '$name' already existing.");
        $secret->use_key_id = $kid;
        $secret->secret_val_enc = $this->encrypt($unencryptedContent, $kid);
        $this->config->save();
    }

    public function storeSecret(string $name, string $unencryptedContent)
    {
        $secret = T_Secret::maintain($this->config->config->secrets, ["name" => $name], false, new NotFoundException("Secret '$name' not defined."));
        $secret->secret_val_enc = $this->encrypt($unencryptedContent, $secret->use_key_id);
        $this->config->save();
    }

    public function getSecret(string $name) : string
    {
        $secret = T_Secret::maintain($this->config->config->secrets, ["name" => $name], false, new NotFoundException("Secret '$name' not found."));
        return $this->decrypt($secret->secret_val_enc, $secret->use_key_id);
    }

    public function isKeyUnlocked(string $kid)
    {
        return T_KeyPair::maintain($this->unlockedKeys, ["key_id" => $kid], false, null) !== null;
    }

    public function unlockKey($kid, string $secret)
    {
        $keyPair = clone T_KeyPair::maintain($this->config->config->keypairs, ["key_id" => $kid], false, new NotFoundException("KeyPair '$kid' not found."));
        $sync = new PhoreSecretBoxSync($secret);

        $keyPair->private_key_enc = $sync->decrypt($keyPair->private_key_enc);
        $this->unlockedKeys[] = $keyPair;
    }

}