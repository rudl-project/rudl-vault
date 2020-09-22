<?php


namespace Rudl\Vault\Cli;


use Phore\CliTools\Cli\CliContext;
use Phore\CliTools\PhoreAbstractCmd;
use Phore\FileSystem\PhoreTempFile;
use Rudl\Vault\Lib\Config;
use Rudl\Vault\Lib\KeyVault;

abstract class VaultSubCmd extends PhoreAbstractCmd
{

    protected $config;
    protected $keyVault;


    public function openInEditor(string $text = "") : string
    {
        $tmpFile = new PhoreTempFile();
        $tmpFile->set_contents($text);
        passthru("editor --not-a-term " . escapeshellarg($tmpFile));
        $content =  $tmpFile->get_contents();
        $tmpFile->unlink();
        return $content;
    }

    public function unlockKey(CliContext $context, string $kid)
    {
        $context->ask("Secret to unlock key_id '$kid': ", null, function ($input) use ($kid) {
            $this->keyVault->unlockKey($kid, $input);
            return true;
        });
    }

    public function __construct(Config $config=null, KeyVault $keyVault=null)
    {
        parent::__construct();
        $this->config = $config;
        $this->keyVault = $keyVault;
    }

}