<?php


namespace Rudl\Vault\Lib;


use Phore\FileSystem\PhoreUri;
use Rudl\Vault\Lib\KeyLoader\KeyLoader;

class Translator
{

    /**
     * @var KeyVault
     */
    private $keyVault;

    /**
     * @var KeyLoader
     */
    private $keyLoader;

    public function __construct(KeyVault $keyVault, KeyLoader $keyLoader)
    {
        $this->keyVault = $keyVault;
        $this->keyLoader = $keyLoader;
    }

    public function translate(string $sourceDir, string $targetDir)
    {
        $sourceDir = phore_dir($sourceDir)->assertDirectory();
        $targetDir = phore_dir($targetDir)->assertDirectory(true);

        $sourceDir->walkR(function (PhoreUri $uri) use ($targetDir) {

        });
    }
}