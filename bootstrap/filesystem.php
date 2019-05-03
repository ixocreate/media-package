<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media;

use Ixocreate\Filesystem\Option\LocalOption;

/** @var \Ixocreate\Filesystem\FilesystemConfigurator $filesystem */
$filesystem->addStorage("media", new LocalOption(\getcwd() . '/data/'));
