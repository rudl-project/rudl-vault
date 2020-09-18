<?php


namespace Rudl\Vault\Lib\Type;

use Phore\Misc\Objects\PhoreObjectMaintainer;

/**
 * Class T_Secret
 * @package Rudl\Vault\Lib\Type
 * @internal
 */
class T_Secret
{
    use PhoreObjectMaintainer;
    /**
     * @var string
     */
    public $name;

    /**
     *
     * @type string|null
     */
    public $desc;

    /**
     * @var string
     */
    public $use_key_id;
    /**
     * @var string
     */
    public $secret_val_enc;
}