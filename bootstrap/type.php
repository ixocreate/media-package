<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media;

use Ixocreate\Media\Schema\Type\AudioType;
use Ixocreate\Media\Schema\Type\DocumentType;
use Ixocreate\Media\Schema\Type\ImageType;
use Ixocreate\Media\Schema\Type\MediaType;
use Ixocreate\Media\Schema\Type\VideoType;
use Ixocreate\Schema\Schema\TypeConfigurator;

/** @var TypeConfigurator $type */
$type->addType(ImageType::class);
$type->addType(MediaType::class);
$type->addType(DocumentType::class);
$type->addType(VideoType::class);
$type->addType(AudioType::class);
