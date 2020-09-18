<?php


namespace Rudl\Vault\Lib\Type;

/**
 * Class T_ConfigFile
 * @package Rudl\Vault\Lib\Type
 * @internal
 */
class T_ConfigFile
{
    /**
     * @var string|null
     */
    public $version = "rudl-vault/1.0";

    /**
     * @var T_KeyPair[]
     */
    public $keypairs = [];

    /**
     *
     * @var T_Secret[]
     */
    public $secrets = [];
}