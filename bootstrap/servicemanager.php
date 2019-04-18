<?php
declare(strict_types=1);

namespace Ixocreate\Media;

use Ixocreate\Application\Service\ServiceManagerConfigurator;
use Ixocreate\Media\Config\Factory\MediaConfigFactory;
use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\Handler\MediaHandlerSubManager;
use Ixocreate\Media\ImageDefinition\ImageDefinitionSubManager;
use Ixocreate\Media\Uri\Factory\MediaUriFactory;
use Ixocreate\Media\Uri\MediaUri;

/** @var ServiceManagerConfigurator $serviceManager */

$serviceManager->addFactory(MediaConfig::class, MediaConfigFactory::class);
$serviceManager->addFactory(MediaUri::class, MediaUriFactory::class);

$serviceManager->addSubManager(MediaHandlerSubManager::class);
$serviceManager->addSubManager(ImageDefinitionSubManager::class);
