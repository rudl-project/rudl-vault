<?php


namespace Rudl\Vault\Lib\Type;

use Phore\Misc\Objects\PhoreObjectMaintainer;

/**
 * Class T_KeyPair
 * @package Rudl\Vault\Lib\Type
 * @internal
 */
class T_KeyPair
{
    use PhoreObjectMaintainer;
    /**
     * @var string
     */
    public $key_id;

    /**
     * @var string|null
     */
    public $desc = "";

    /**
     * @var string
     */
    public $private_key_enc;

    /**
     * @var string
     */
    public $public_key;
}