<?php
declare(strict_types=1);

namespace Ixocreate\Package\Media;

use Ixocreate\Package\Media\Uri\Factory\UriFactory;
use Ixocreate\Package\Media\Uri\Uri;
use Ixocreate\ServiceManager\ServiceManagerConfigurator;
use Ixocreate\Package\Media\Config\MediaConfig;
use Ixocreate\Package\Media\Config\Factory\MediaConfigFactory;
use Ixocreate\Package\Media\Delegator\DelegatorSubManager;
use Ixocreate\Package\Media\ImageDefinition\ImageDefinitionSubManager;

/** @var ServiceManagerConfigurator $serviceManager */
$serviceManager->addFactory(MediaConfig::class, MediaConfigFactory::class);
$serviceManager->addFactory(Uri::class, UriFactory::class);

$serviceManager->addSubManager(DelegatorSubManager::class);
$serviceManager->addSubManager(ImageDefinitionSubManager::class);
