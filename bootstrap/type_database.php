<?php
declare(strict_types=1);

namespace KiwiSuite\Media;

/** @var \KiwiSuite\Database\Type\TypeConfigurator $type */

use Doctrine\DBAL\Types\GuidType;
use KiwiSuite\Media\Type\ImageType;

$type->addType(ImageType::class, GuidType::class);
