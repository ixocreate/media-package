<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media;

use Ixocreate\Media\Type\AudioType;
use Ixocreate\Media\Type\DocumentType;
use Ixocreate\Media\Type\ImageType;
use Ixocreate\Media\Type\MediaType;
use Ixocreate\Media\Type\VideoType;
use Ixocreate\Type\TypeConfigurator;

/** @var TypeConfigurator $type */
$type->addType(ImageType::class);
$type->addType(MediaType::class);
$type->addType(DocumentType::class);
$type->addType(VideoType::class);
$type->addType(AudioType::class);
