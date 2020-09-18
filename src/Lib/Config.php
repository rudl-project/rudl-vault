<?php


namespace Rudl\Vault\Lib;


use Phore\FileSystem\PhoreFile;
use Rudl\Vault\Lib\Type\T_ConfigFile;

class Config
{

    const DEFAULT_NAME = ".rudl-vault.json";

    /**
     * @var PhoreFile
     */
    private $file;

    /**
     * @var T_ConfigFile
     */
    public $config = null;

    public function load($filename)
    {
        $this->file = phore_file($filename);
        $this->config = phore_hydrate($this->file->get_json(), T_ConfigFile::class);
    }

    public static function findConfigFile($path) : ?PhoreFile
    {
        $curPath = phore_dir($path);
        do {
            if ($curPath->withFileName(self::DEFAULT_NAME)->exists())
                return $curPath->withFileName(self::DEFAULT_NAME);
            $curPath = phore_dir($curPath->getDirname());
        } while ((string)$curPath !== "/");
        return null;
    }

    public function createNew($filename)
    {
        $this->config = new T_ConfigFile();
        $this->file = phore_file($filename);
    }

    public function findRecursive($path)
    {

    }


    public function save()
    {
        $this->file->set_contents(
            phore_json_pretty_print(
                phore_json_encode((array)$this->config),
                "  "
            )
        );
    }

}