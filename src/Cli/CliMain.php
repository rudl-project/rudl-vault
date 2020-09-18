<?php


namespace Rudl\Vault\Cli;


use Phore\CliTools\Helper\GetOptResult;
use Phore\CliTools\PhoreAbstractCli;
use Phore\FileSystem\PhoreDirectory;
use Phore\FileSystem\PhoreTempFile;
use Rudl\Vault\Lib\Config;
use Rudl\Vault\Lib\Ex\KeyNotUnlockedException;
use Rudl\Vault\Lib\KeyVault;
use Rudl\Vault\Lib\Type\T_KeyPair;

class CliMain extends PhoreAbstractCli
{

    /**
     * @var PhoreDirectory
     */
    protected $workdir;

    /**
     * @var Config|null
     */
    protected $config;

    /**
     * @var KeyVault
     */
    protected $keyVault;

    public function __construct()
    {
        parent::__construct(
            "Rudl Key Vault",
            "",
            "hv",
            []
        );
    }

    public function initCmd()
    {

        if ($this->config !== null) {
            $this->emergency("key-vault file already existing.");
            return;
        }
        $newFile = $this->workdir->withFileName(Config::DEFAULT_NAME);
        $newFile = $this->ask("Specify new location ($newFile): ", $newFile);

        $config = new Config();
        $config->createNew($newFile);
        $config->save();
        $this->out("Created rudl vault in $newFile");
    }

    public function createKeyPairCmd()
    {
        $keyId = $this->ask("New name for new key: ", null, "/^[a-zA-Z0-9]+$/");
        $passphrase = $this->ask("Specify secret for private key (leave blanc to generate): ", "");

        $this->out("\n");
        if ($passphrase === "") {
            $passphrase = phore_random_str(24);
            $this->out("Generated secret: $passphrase\n");
        }

        $this->keyVault->createKeyPair($keyId, $passphrase);

        $this->out("New keypair '$keyId' was generated. You can now add the environment variable:\n\n");
        $this->out("RUDL_VAULT_SECRET_$keyId=$passphrase\n\nto unlock automatically.\n");
    }

    public function unlockKey($kid)
    {
        $this->ask("Secret to unlock key_id '$kid': ", null, function ($input) use ($kid) {
            $this->keyVault->unlockKey($kid, $input);
            return true;
        });
    }


    public function createSecretCmd(array $argv)
    {
        $name = $argv[1] ?? $this->ask("The name of your new secret: ", null);

        $kid = $this->keyVault->defaultKeyPairId();
        $keysAvail = phore_misc_array($this->config->config->keypairs)
            ->map(fn($i,T_KeyPair $data) => $data->key_id);

        if ($kid === null) {
            $this->out("Avail key_ids: {$keysAvail->join(", ")}\n");
            $kid = $this->ask("Which key_id do you want to create the secret with? ", null, fn($kid) => $keysAvail->inArray($kid) ? $kid : null);
        }

        $tmp = new PhoreTempFile();
        passthru("editor --not-a-term " . escapeshellarg($tmp), $ret);
        if ($ret !== 0)
            echo "Abort $ret";

        $this->keyVault->createSecret($name, $tmp->get_contents(), $kid);
        $tmp->unlink();

        $this->out("Secret '$name' stored with key_id '$kid'");
    }

    public function editSecretCmd(array $argv)
    {
        $name = $argv[0] ?? $this->ask("Specify the secret to edit: ");
        while(true) {
            try {
                $tmpFile = new PhoreTempFile();
                $tmpFile->set_contents($this->keyVault->getSecret($name));
                passthru("editor --not-a-term " . escapeshellarg($tmpFile));
                $this->keyVault->storeSecret($name, $tmpFile->get_contents());
                $tmpFile->unlink();
                return;
            } catch (KeyNotUnlockedException $e) {
                $this->unlockKey($e->getKeyId());
                continue;
            }
        }

    }

    protected function main(array $argv, int $argc, GetOptResult $opts)
    {
        $this->workdir = phore_dir(getcwd());
        if (getenv("RUDL_VAULT_WORKDIR") !== false)
            $this->workdir = phore_dir(getenv("RUDL_VAULT_WORKDIR"));

        $configFile = Config::findConfigFile($this->workdir);
        if ($configFile !== null) {
            $this->out("Using vault-config '$configFile'\n");
            $this->config = new Config();
            $this->config->load($configFile);
            $this->keyVault = new KeyVault($this->config);
        }

        $this->execMap([
            "init" => [&$this, "initCmd"],
            "create-keypair" => [&$this, "createKeyPairCmd"],
            "secret-create" => [&$this, "createSecretCmd"],
            "secret-edit" => [&$this, "editSecretCmd"]
        ]);
        $this->out("\n");
    }
}