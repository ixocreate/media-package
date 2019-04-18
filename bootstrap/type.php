<?php
declare(strict_types=1);

namespace Ixocreate\Package\Media;


use Ixocreate\Package\Media\Type\DocumentType;
use Ixocreate\Package\Media\Type\AudioType;
use Ixocreate\Package\Media\Type\ImageType;
use Ixocreate\Package\Media\Type\MediaType;
use Ixocreate\Package\Entity\Type\TypeConfigurator;
use Ixocreate\Package\Media\Type\VideoType;

/** @var TypeConfigurator $type */

$type->addType(ImageType::class);
$type->addType(MediaType::class);
$type->addType(DocumentType::class);
$type->addType(VideoType::class);
$type->addType(AudioType::class);
