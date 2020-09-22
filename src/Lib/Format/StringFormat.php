<?php


namespace Rudl\Vault\Lib\Format;


use Rudl\Vault\Lib\KeyLoader\KeyLoader;
use Rudl\Vault\Lib\KeyVault;

class StringFormat
{

    /**
     * @var KeyVault
     */
    private $keyVault;

    /**
     * @var KeyLoader
     */
    private $keyLoader;

    public function __construct(KeyVault $keyVault, KeyLoader $keyLoader=null)
    {
        $this->keyVault = $keyVault;
        $this->keyLoader = $keyLoader;
    }

    public function encode(string $input, string $key_id) : string
    {
        return "{RSEC1.{$key_id}." . phore_base64url_encode($this->keyVault->encrypt($input, $key_id)) . "}";
    }

    public function decode(string $input) : string
    {
        return phore_misc_string($input)->regex("/\{RSEC1\.(?<keyId>[a-zA-Z0-9_-]+)\.(?<enc>.*?)\}/m")
            ->replaceCallback(function ($keyId, $enc) {
                if ( ! $this->keyVault->isKeyUnlocked($keyId)) {
                    $this->keyLoader->loadKey($keyId, $this->keyVault);
                }
                return $this->keyVault->decrypt(phore_base64url_decode($enc), $keyId);
            });
    }

}