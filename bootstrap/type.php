<?php
declare(strict_types=1);

namespace Ixocreate\Media\Package;


use Ixocreate\Media\Package\Type\DocumentType;
use Ixocreate\Media\Package\Type\AudioType;
use Ixocreate\Media\Package\Type\ImageType;
use Ixocreate\Media\Package\Type\MediaType;
use Ixocreate\Entity\Package\Type\TypeConfigurator;
use Ixocreate\Media\Package\Type\VideoType;

/** @var TypeConfigurator $type */

$type->addType(ImageType::class);
$type->addType(MediaType::class);
$type->addType(DocumentType::class);
$type->addType(VideoType::class);
$type->addType(AudioType::class);
