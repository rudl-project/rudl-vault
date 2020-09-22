<?php


namespace Rudl\Vault\Cli\Crypt;


use Phore\CliTools\Cli\CliContext;
use Phore\CliTools\Helper\GetOptResult;
use Rudl\Vault\Cli\VaultSubCmd;
use Rudl\Vault\Lib\Format\StringFormat;
use Rudl\Vault\Lib\Type\T_KeyPair;

class EncryptCmd extends VaultSubCmd
{

    public function invoke(CliContext $context)
    {
        $opts = $context->getOpts("k:", ["stdin"]);

        $kid = $opts->get("k") ?? $this->keyVault->defaultKeyPairId();
        $keysAvail = phore_misc_array($this->config->config->keypairs)
            ->map(fn($i,T_KeyPair  $data) => $data->key_id);

        if ($kid === null) {
            $context->out("Avail key_ids: {$keysAvail->join(", ")}\n");
            $kid = $context->ask("Which key_id do you want to create the secret with? ", null, fn($kid) => $keysAvail->inArray($kid) ? $kid : null);
        }

        $secret = $opts->argv("0");
        if ($secret === null)
            $secret = $this->openInEditor();

        $format = new StringFormat($this->keyVault);

        $context->stdout($format->encode($secret, $kid));
        $context->out("\n");
        return 0;

    }
}