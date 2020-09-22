<?php


namespace Rudl\Vault\Cli\Glob;


use Phore\CliTools\Cli\CliContext;
use Phore\CliTools\Helper\GetOptResult;
use Phore\CliTools\PhoreAbstractCmd;
use Phore\FileSystem\PhoreTempFile;
use Rudl\Vault\Cli\VaultSubCmd;
use Rudl\Vault\Lib\Type\T_KeyPair;

class SecretCreateCmd extends VaultSubCmd
{

    public function invoke(CliContext $context)
    {
        $opts = $context->getOpts();

        $name = $opts->argv(0) ?? $context->ask("The name of your new secret: ", null);

        $kid = $this->keyVault->defaultKeyPairId();
        $keysAvail = phore_misc_array($this->config->config->keypairs)
            ->map(fn($i,T_KeyPair $data) => $data->key_id);

        if ($kid === null) {
            $context->out("Avail key_ids: {$keysAvail->join(", ")}\n");
            $kid = $context->ask("Which key_id do you want to create the secret with? ", null, fn($kid) => $keysAvail->inArray($kid) ? $kid : null);
        }

        $newSecret = $this->openInEditor();
        $this->keyVault->createSecret($name, $newSecret, $kid);

        $context->out("Secret '$name' stored with key_id '$kid'");
    }
}