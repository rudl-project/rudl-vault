<?php


namespace Rudl\Vault\Lib\KeyLoader;


use Rudl\Vault\Lib\KeyVault;

interface KeyLoader
{
    public function loadKey(string $key_id, KeyVault $keyVault) : void ;

}