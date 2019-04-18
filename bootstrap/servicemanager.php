<?php
declare(strict_types=1);

namespace Ixocreate\Media;

use Ixocreate\Media\Uri\Factory\UriFactory;
use Ixocreate\Media\Uri\Uri;
use Ixocreate\ServiceManager\ServiceManagerConfigurator;
use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\Config\Factory\MediaConfigFactory;
use Ixocreate\Media\Delegator\DelegatorSubManager;
use Ixocreate\Media\ImageDefinition\ImageDefinitionSubManager;

/** @var ServiceManagerConfigurator $serviceManager */
$serviceManager->addFactory(MediaConfig::class, MediaConfigFactory::class);
$serviceManager->addFactory(Uri::class, UriFactory::class);

$serviceManager->addSubManager(DelegatorSubManager::class);
$serviceManager->addSubManager(ImageDefinitionSubManager::class);
