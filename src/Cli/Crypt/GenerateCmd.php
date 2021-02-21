<?php


namespace Rudl\Vault\Cli\Crypt;


use Phore\CliTools\Cli\CliContext;
use Phore\CliTools\Helper\GetOptResult;
use Rudl\Vault\Cli\VaultSubCmd;
use Rudl\Vault\Lib\Format\StringFormat;
use Rudl\Vault\Lib\Type\T_KeyPair;

class GenerateCmd extends VaultSubCmd
{

    public function invoke(CliContext $context)
    {
        $opts = $context->getOpts("k:U", ["stdin"]);

        $kid = $opts->get("k") ?? $this->keyVault->defaultKeyPairId();
        $keysAvail = phore_misc_array($this->config->config->keypairs)
            ->map(fn($i,T_KeyPair  $data) => $data->key_id);

        if ($kid === null) {
            $context->out("Avail key_ids: {$keysAvail->join(", ")}\n");
            $kid = $context->ask("Which key_id do you want to create the secret with? ", null, fn($kid) => $keysAvail->inArray($kid) ? $kid : null);
        }

        $secret = phore_random_str(24);

        $format = new StringFormat($this->keyVault);

        $context->stdout("\nCreated new random secret:\n\n");
        if ($opts->has("U")) {
            $context->stdout("Plain.: $secret\n");
        } else {
            $context->stdout("Plain.: [masked - specify option -U to show plain secret]\n");
        }
        $context->stdout("Sealed: " . $format->encode($secret, $kid) . "\n");
        $context->stdout("\n");
        $context->stdout("SHA512..: " . crypt($secret, '$6$rounds=5000$' . phore_random_str(16) . '$') . "\n");
        $context->stdout("Blowfish: " . password_hash($secret, PASSWORD_BCRYPT) . "\n");
        $context->stdout("Argon2I.: " . password_hash($secret, PASSWORD_ARGON2I) . "\n");
        $context->stdout("Argon2ID: " . password_hash($secret, PASSWORD_ARGON2ID) . "\n");
        $context->out("\n");
        return 0;

    }
}