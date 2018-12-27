<?php
/**
 * @see https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\BootstrapItem;

use Ixocreate\Contract\Application\BootstrapItemInterface;
use Ixocreate\Contract\Application\ConfiguratorInterface;
use Ixocreate\Media\Delegator\DelegatorConfigurator;

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
