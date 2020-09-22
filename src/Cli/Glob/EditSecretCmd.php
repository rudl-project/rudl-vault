<?php


namespace Rudl\Vault\Cli\Glob;


use Phore\CliTools\Cli\CliContext;
use Phore\CliTools\Ex\UserInputException;
use Phore\CliTools\Helper\GetOptResult;
use Phore\CliTools\PhoreAbstractCmd;
use Phore\Core\Exception\NotFoundException;
use Phore\FileSystem\PhoreTempFile;
use Rudl\Vault\Cli\VaultSubCmd;
use Rudl\Vault\Lib\Ex\KeyNotUnlockedException;

class EditSecretCmd extends VaultSubCmd
{

    public function invoke(CliContext $context)
    {
        $opts = $context->getOpts();
        $name = $opts->argv(0) ?? $context->ask("Specify the secret to edit: ");

        while(true) {
            try {
                $newSecret = $this->openInEditor($this->keyVault->getSecret($name));
                $this->keyVault->storeSecret($name, $newSecret);
                return 0;
            } catch (KeyNotUnlockedException $e) {
                $this->unlockKey($context, $e->getKeyId());
                continue;
            } catch (NotFoundException $e) {
                throw new UserInputException($e->getMessage());
            }
        }
    }
}