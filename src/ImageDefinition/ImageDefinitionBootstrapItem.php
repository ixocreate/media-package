<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\ImageDefinition;

use Ixocreate\Application\BootstrapItemInterface;
use Ixocreate\Application\ConfiguratorInterface;

final class ImageDefinitionBootstrapItem implements BootstrapItemInterface
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
        return 'image-definition.php';
    }
}
