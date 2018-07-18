<?php
declare(strict_types=1);

namespace KiwiSuite\Media;

use KiwiSuite\Media\Uri\Factory\UriFactoy;
use KiwiSuite\Media\Uri\Uri;
use KiwiSuite\ServiceManager\ServiceManagerConfigurator;
use KiwiSuite\Media\Config\MediaConfig;
use KiwiSuite\Media\Config\Factory\MediaConfigFactory;
use KiwiSuite\Media\Delegator\DelegatorSubManager;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionSubManager;

/** @var ServiceManagerConfigurator $serviceManager */
$serviceManager->addFactory(MediaConfig::class, MediaConfigFactory::class);
$serviceManager->addFactory(Uri::class, UriFactoy::class);

$serviceManager->addSubManager(DelegatorSubManager::class);
$serviceManager->addSubManager(ImageDefinitionSubManager::class);
