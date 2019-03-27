<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
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
