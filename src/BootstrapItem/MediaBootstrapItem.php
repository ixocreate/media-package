<?php
/**
 * kiwi-suite/media (https://github.com/kiwi-suite/media)
 *
 * @package kiwi-suite/media
 * @see https://github.com/kiwi-suite/media
 * @copyright Copyright (c) 2010 - 2018 kiwi suite GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\BootstrapItem;

use Ixocreate\Contract\Application\BootstrapItemInterface;
use Ixocreate\Contract\Application\ConfiguratorInterface;
use Ixocreate\Media\Config\MediaConfigurator;

final class MediaBootstrapItem implements BootstrapItemInterface
{
    public function getConfigurator(): ConfiguratorInterface
    {
        return new MediaConfigurator();
    }
    public function getVariableName(): string
    {
        return 'media';
    }
    public function getFileName(): string
    {
        return 'media.php';
    }
}
