<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Package\Bootstrap;

use Ixocreate\Application\Service\Bootstrap\BootstrapItemInterface;
use Ixocreate\Application\Service\Configurator\ConfiguratorInterface;
use Ixocreate\Media\Package\ImageDefinition\ImageDefinitionConfigurator;

final class MediaDefinitionBootstrapItem implements BootstrapItemInterface
{
    /**
     * @return ConfiguratorInterface
     */
    public function getConfigurator(): ConfiguratorInterface
    {
        return new ImageDefinitionConfigurator();
    }

    /**
     * @return string
     */
    public function getVariableName(): string
    {
        return 'imageDefinition';
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return 'media-definition.php';
    }
}
