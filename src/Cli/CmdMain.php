<?php


namespace Rudl\Vault\Cli;


use Phore\CliTools\Cli\CliContext;
use Phore\CliTools\Helper\GetOptResult;
use Phore\CliTools\PhoreAbstractCmd;
use Phore\CliTools\PhoreAbstractMainCmd;
use Phore\FileSystem\PhoreDirectory;
use Phore\FileSystem\PhoreTempFile;
use Rudl\Vault\Cli\Crypt\EncryptCmd;
use Rudl\Vault\Cli\Crypt\GenerateCmd;
use Rudl\Vault\Cli\Crypt\InspectCmd;
use Rudl\Vault\Cli\Glob\EditSecretCmd;
use Rudl\Vault\Cli\Glob\SecretCreateCmd;
use Rudl\Vault\Cli\Init\CreateKeyPairCmd;
use Rudl\Vault\Cli\Init\InitCmd;
use Rudl\Vault\Lib\Config;
use Rudl\Vault\Lib\Ex\KeyNotUnlockedException;
use Rudl\Vault\Lib\KeyVault;
use Rudl\Vault\Lib\Type\T_KeyPair;

class CmdMain extends PhoreAbstractMainCmd
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






    public function invoke(CliContext $context) : int
    {
        $opts = $context->getOpts("h");

        if ($opts->has("h"))
            $context->printHelpAndExit(__DIR__ . "/help.txt");

        $this->workdir = $context->getEnv("RUDL_VAULT_WORKDIR", getcwd());
        $context->debug("Using workdir '$this->workdir'");

        $configFile = Config::findConfigFile($this->workdir);
        if ($configFile !== null) {
            $context->out("Using vault-config '$configFile'\n");
            $this->config = new Config();
            $this->config->load($configFile);
            $this->keyVault = new KeyVault($this->config);
        }

        return $context->dispatchMap([
            "init" => new InitCmd($this->workdir, $this->config),
            "create-key-pair" => new CreateKeyPairCmd($this->config, $this->keyVault),
            "secret" => [
                "generate" => new GenerateCmd($this->config, $this->keyVault),
                "create" => new SecretCreateCmd($this->config, $this->keyVault),
                "edit" => new EditSecretCmd($this->config, $this->keyVault)
            ],
            "encrypt" => new EncryptCmd($this->config, $this->keyVault),
            "inspect" => new InspectCmd($this->config, $this->keyVault)
        ], $opts);

    }


}