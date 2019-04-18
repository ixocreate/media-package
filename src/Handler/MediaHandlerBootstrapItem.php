<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Handler;

use Ixocreate\Application\Bootstrap\BootstrapItemInterface;
use Ixocreate\Application\Configurator\ConfiguratorInterface;

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
