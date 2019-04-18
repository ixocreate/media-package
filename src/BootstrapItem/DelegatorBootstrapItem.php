<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Package\Media\BootstrapItem;

use Ixocreate\Application\BootstrapItemInterface;
use Ixocreate\Application\ConfiguratorInterface;
use Ixocreate\Package\Media\Delegator\DelegatorConfigurator;

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
