<?php


namespace Rudl\Vault\Lib\KeyLoader;


use Rudl\Vault\Lib\KeyVault;

class CallbackKeyLoader implements KeyLoader
{

    private $cb;
    public function __construct(callable  $cb)
    {
        $this->cb = $cb;
    }

    public function loadKey(string $key_id, KeyVault $keyVault) : void
    {
        ($this->cb)($key_id, $keyVault);
    }
}