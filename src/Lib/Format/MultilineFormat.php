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
        $line = "===== Start RudlVault Sealed Content - KeyId: $key_id =====\n";
        $line .= chunk_split(phore_base64url_encode($this->keyVault->encrypt($input, $key_id)));
        $line .= "==== End RudlVault Sealed Content =====\n";
        return $line;
    }

    public function decode(string $input) : string
    {
        $parseText = trim (str_replace("\r\n", "\n", $input));

        $exp = explode("\n", $parseText);
        $sealedContent = "";
        if (preg_match("/^\={3,}.*?KeyId: (?<keyId>[a-zA-Z0-9_-]+)/", trim($exp[0]), $matches)) {
            $keyId = $matches["keyId"];
            for ($i = 1; $i<count ($exp); $i++) {
                if (preg_match("/^={3,}/", trim ($exp[$i]))) {
                    break;
                }
                $sealedContent .= trim ($exp[$i]);
            }
            if ( ! $this->keyVault->isKeyUnlocked($keyId)) {
                $this->keyLoader->loadKey($keyId, $this->keyVault);
            }
            return $this->keyVault->decrypt(phore_base64url_decode($sealedContent), $keyId);
        }
        return $input;
    }
}