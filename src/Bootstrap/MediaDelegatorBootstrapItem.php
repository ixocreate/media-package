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
use Ixocreate\Media\Package\Delegator\DelegatorConfigurator;

final class MediaDelegatorBootstrapItem implements BootstrapItemInterface
{
    /**
     * @return mixed
     */
    public function getConfigurator(): ConfiguratorInterface
    {
        return new DelegatorConfigurator();
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return 'media-delegate.php';
    }

    /**
     * @return string
     */
    public function getVariableName(): string
    {
        return 'mediaDelegator';
    }
}
