<?php


namespace Rudl\Vault\Lib\Format;


use Rudl\Vault\Lib\KeyLoader\KeyLoader;
use Rudl\Vault\Lib\KeyVault;

class MultilineFormat
{
    /**
     * @var KeyVault
     */
    private $keyVault;

    /**
     * @var KeyLoader
     */
    private $keyLoader;

    public function __construct(KeyVault $keyVault, KeyLoader $keyLoader=null)
    {
        $this->keyVault = $keyVault;
        $this->keyLoader = $keyLoader;
    }

    public function encode(string $input, string $key_id) : string
    {
        $line = "\n===== Start RudlVault Encoded Content - KeyId: $key_id =====\n";
        $line .= chunk_split(phore_base64url_encode($this->keyVault->encrypt($input, $key_id)));
        $line .= "\n==== End RudlVault Encoded Content =====";
        return $line;
    }

    public function decode(string $input) : string
    {
        $input = str_replace("\r\n", \n, $input);
        return phore_misc_string($input)->regex("/\n={3,}.*?KeyId: (?<keyId>[a-zA-Z0-9_-]+)={3,}\n(?<enc>.*?)\n={3,}.*?={3,}\n/m")
            ->replaceCallback(function ($keyId, $enc) {
                $enc = str_replace("\n", "", $enc);
                if ( ! $this->keyVault->isKeyUnlocked($keyId)) {
                    $this->keyLoader->loadKey($keyId, $this->keyVault);
                }
                return $this->keyVault->decrypt(phore_base64url_decode($enc), $keyId);
            });
    }
}