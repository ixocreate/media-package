<?php
declare(strict_types=1);

use Symfony\Component\Console\Command\Command;
use KiwiSuite\Contract\Command\CommandInterface;

class CreateImageDefinitionCommand extends Command implements CommandInterface
{
    public static function getCommandName()
    {
        return "media:create-imageDefinition";
    }
}