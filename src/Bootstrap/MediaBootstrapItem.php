<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Bootstrap;

use Ixocreate\Application\Service\Bootstrap\BootstrapItemInterface;
use Ixocreate\Application\Service\Configurator\ConfiguratorInterface;
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
