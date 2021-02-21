<?php


namespace Rudl\Vault\Cli\Init;


use Phore\CliTools\Cli\CliContext;

use Rudl\Vault\Cli\VaultSubCmd;

class CreateKeyPairCmd extends VaultSubCmd
{

    public function invoke(CliContext $context)
    {
        $opts = $context->getOpts();
        $keyId = $opts->argv(0) ?? $context->ask("New name for new key (default): ", "default", "/^[a-zA-Z0-9]+$/");
        $passphrase = $context->ask("Specify secret for private key (leave blanc to generate): ", "");

        $context->out("\n");
        if ($passphrase === "") {
            $passphrase = phore_random_str(64);
            $context->out("Generated secret: $passphrase\n");
        }

        $this->keyVault->createKeyPair($keyId, $passphrase);

        $context->out("New keypair '$keyId' was generated. You can now add the environment variable:\n\n");
        $context->out("RUDL_VAULT_SECRET_" . strtoupper($keyId) . "=$passphrase\n\nto unlock automatically.\n");
        return 0;
    }
}