<?php


namespace Rudl\Vault\Lib\Ex;


class KeyNotUnlockedException extends \Exception
{
    public $keyId;
    public function __construct(string $key_id) {
        parent::__construct("Key_id '$key_id' not unlocked.");
        $this->keyId = $key_id;
    }

    public function getKeyId() : string
    {
        return $this->keyId;
    }
}