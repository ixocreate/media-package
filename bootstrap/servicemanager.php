<?php
declare(strict_types=1);

namespace KiwiSuite\Media;

use KiwiSuite\ServiceManager\ServiceManagerConfigurator;
use KiwiSuite\Media\MediaConfig;
use KiwiSuite\Media\Factory\MediaConfigFactory;
use KiwiSuite\Media\Delegator\DelegatorSubManager;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionSubManager;

/** @var ServiceManagerConfigurator $serviceManager */
$serviceManager->addFactory(MediaConfig::class, MediaConfigFactory::class);

$serviceManager->addSubManager(DelegatorSubManager::class);
$serviceManager->addSubManager(ImageDefinitionSubManager::class);