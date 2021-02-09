<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media;

use Ixocreate\CommandBus\CommandBusConfigurator;
use Ixocreate\Media\Command\Image\EditorCommand;
use Ixocreate\Media\Command\Media\CreateCommand;
use Ixocreate\Media\Command\Media\DeleteCommand;
use Ixocreate\Media\Command\Media\UpdateCommand;

/** @var CommandBusConfigurator $commandBus */
$commandBus->addCommand(EditorCommand::class);
$commandBus->addCommand(CreateCommand::class);
$commandBus->addCommand(DeleteCommand::class);
$commandBus->addCommand(UpdateCommand::class);
