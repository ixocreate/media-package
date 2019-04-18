<?php
declare(strict_types=1);

namespace Ixocreate\Media\Package;

use Ixocreate\Media\Package\Uri\Factory\UriFactory;
use Ixocreate\Media\Package\Uri\Uri;
use Ixocreate\ServiceManager\ServiceManagerConfigurator;
use Ixocreate\Media\Package\Config\MediaConfig;
use Ixocreate\Media\Package\Config\Factory\MediaConfigFactory;
use Ixocreate\Media\Package\Delegator\DelegatorSubManager;
use Ixocreate\Media\Package\ImageDefinition\ImageDefinitionSubManager;

/** @var ServiceManagerConfigurator $serviceManager */
$serviceManager->addFactory(MediaConfig::class, MediaConfigFactory::class);
$serviceManager->addFactory(Uri::class, UriFactory::class);

$serviceManager->addSubManager(DelegatorSubManager::class);
$serviceManager->addSubManager(ImageDefinitionSubManager::class);
