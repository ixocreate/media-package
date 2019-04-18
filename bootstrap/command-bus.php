<?php

namespace Ixocreate\Media;

use Ixocreate\CommandBus\CommandBusConfigurator;

/** @var CommandBusConfigurator $commandBus */

$commandBus->addCommandDirectory(__DIR__ . '/../src/Command', true);
