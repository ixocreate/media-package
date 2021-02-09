<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media;

use Ixocreate\Application\Console\ConsoleConfigurator;
use Ixocreate\Media\Console\DisplayImageDefinition;
use Ixocreate\Media\Console\MoveAllToPublicStatusCommand;
use Ixocreate\Media\Console\MoveByPublicStatusCommand;
use Ixocreate\Media\Console\RegenerateDefinitionCommand;

/** @var ConsoleConfigurator $console */
$console->addCommand(DisplayImageDefinition::class);
$console->addCommand(MoveAllToPublicStatusCommand::class);
$console->addCommand(MoveByPublicStatusCommand::class);
$console->addCommand(RegenerateDefinitionCommand::class);
