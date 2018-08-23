<?php
declare(strict_types=1);

namespace KiwiSuite\Media;


use KiwiSuite\Media\Type\ApplicationType;
use KiwiSuite\Media\Type\AudioType;
use KiwiSuite\Media\Type\ImageType;
use KiwiSuite\Media\Type\MediaType;
use KiwiSuite\Entity\Type\TypeConfigurator;
use KiwiSuite\Media\Type\VideoType;

/** @var TypeConfigurator $type */

$type->addType(ImageType::class);
$type->addType(MediaType::class);
$type->addType(ApplicationType::class);
$type->addType(VideoType::class);
$type->addType(AudioType::class);