<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Package\BootstrapItem;

use Ixocreate\Application\Bootstrap\BootstrapItemInterface;
use Ixocreate\Application\ConfiguratorInterface;
use Ixocreate\Media\Package\Delegator\DelegatorConfigurator;

final class DelegatorBootstrapItem implements BootstrapItemInterface
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
        return 'delegator.php';
    }

    /**
     * @return string
     */
    public function getVariableName(): string
    {
        return 'delegator';
    }
}
