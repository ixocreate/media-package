<?php
declare(strict_types=1);

namespace KiwiSuite\Media\BootstrapItem;

use KiwiSuite\Contract\Application\BootstrapItemInterface;
use KiwiSuite\Contract\Application\ConfiguratorInterface;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionConfigurator;

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
        return 'imageDefinition.php';
    }
}