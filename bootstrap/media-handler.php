<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media;

use Ixocreate\Media\Handler\ImageHandler;
use Ixocreate\Media\Handler\MediaHandlerConfigurator;

/** @var MediaHandlerConfigurator $media */
$media->addHandler(ImageHandler::class);
