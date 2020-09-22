<?php


namespace Rudl\Vault\Cli\Init;


use Phore\CliTools\Cli\CliContext;
use Phore\CliTools\Helper\GetOptResult;
use Phore\CliTools\PhoreAbstractCmd;
use Rudl\Vault\Cli\VaultSubCmd;
use Rudl\Vault\Lib\Config;
use Rudl\Vault\Lib\KeyVault;

class InitCmd extends PhoreAbstractCmd
{

    protected $workdir;
    protected $config;

    public function __construct(string $workDir, Config $config = null)
    {
        parent::__construct();
        $this->workdir = $workDir;
        $this->config = $config;
    }

    public function invoke(CliContext $context) : int
    {
        if ($this->config !== null) {
            $context->emergency("key-vault file already existing.");
            return 5;
        }
        $newFile = phore_dir($this->workdir)->withFileName(Config::DEFAULT_NAME);
        $newFile = $context->ask("Specify new location ($newFile): ", $newFile);

        $config = new Config();
        $config->createNew($newFile);
        $config->save();
        $context->out("Created rudl vault in $newFile");
        return 0;
    }
}