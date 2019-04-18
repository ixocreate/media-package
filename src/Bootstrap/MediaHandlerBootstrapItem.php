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
use Ixocreate\Media\Handler\MediaHandlerConfigurator;

final class MediaHandlerBootstrapItem implements BootstrapItemInterface
{
    /**
     * @return mixed
     */
    public function getConfigurator(): ConfiguratorInterface
    {
        return new MediaHandlerConfigurator();
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return 'media-handler.php';
    }

    /**
     * @return string
     */
    public function getVariableName(): string
    {
        return 'media';
    }
}
