<?php
declare(strict_types=1);

namespace Ixocreate\Media;

use Ixocreate\Media\Uri\Factory\MediaUriFactory;
use Ixocreate\Media\Uri\MediaUri;
use Ixocreate\Application\Service\Manager\ServiceManagerConfigurator;
use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\Config\Factory\MediaConfigFactory;
use Ixocreate\Media\Handler\MediaHandlerSubManager;
use Ixocreate\Media\ImageDefinition\ImageDefinitionSubManager;

/** @var ServiceManagerConfigurator $serviceManager */
$serviceManager->addFactory(MediaConfig::class, MediaConfigFactory::class);
$serviceManager->addFactory(MediaUri::class, MediaUriFactory::class);

$serviceManager->addSubManager(MediaHandlerSubManager::class);
$serviceManager->addSubManager(ImageDefinitionSubManager::class);
