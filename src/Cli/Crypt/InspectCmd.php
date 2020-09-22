<?php


namespace Rudl\Vault\Cli\Crypt;


use Grpc\Call;
use Phore\CliTools\Cli\CliContext;
use Phore\CliTools\Ex\UserInputException;
use Phore\CliTools\Helper\GetOptResult;
use Rudl\Vault\Cli\VaultSubCmd;
use Rudl\Vault\Lib\Ex\KeyNotUnlockedException;
use Rudl\Vault\Lib\Format\StringFormat;
use Rudl\Vault\Lib\KeyLoader\CallbackKeyLoader;
use Rudl\Vault\Lib\KeyLoader\KeyLoader;
use Rudl\Vault\Lib\KeyVault;

class InspectCmd extends VaultSubCmd
{

    public  $context;
    public function invoke(CliContext $context)
    {
        $this->context = $context;
        $opts = $context->getOpts("o:");

        $filename = $opts->argv(0, new UserInputException("Please specify a filename to inspect"));
        $inFile = phore_file($filename);
        if ( ! $inFile->isFile())
            throw new UserInputException("Cannot open file '$inFile'");


        $reader = new StringFormat($this->keyVault, new CallbackKeyLoader(function (string $keyId, KeyVault $vault) use ($context) {
            $key = $context->getEnv("RUDL_VAULT_SECRET_" . strtoupper($keyId), null);
            if ($key === null)
                $key = $context->ask("Please enter secret to unlock key '$keyId': ");
            $vault->unlockKey($keyId, $key);
        }));
        $unsealedData = $reader->decode($inFile->get_contents());

        if ($opts->has("o")) {
            phore_file($opts->get("o"))->set_contents($unsealedData);
            return 0;
        }
        $context->stdout($unsealedData);

        return 0;
    }
}