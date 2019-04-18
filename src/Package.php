<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Package;

use Ixocreate\Application\ConfiguratorRegistryInterface;
use Ixocreate\Application\Package\PackageInterface;
use Ixocreate\Application\ServiceRegistryInterface;
use Ixocreate\ServiceManager\ServiceManagerInterface;
use Ixocreate\Media\Package\Bootstrap\DelegatorBootstrapItem;
use Ixocreate\Media\Package\Bootstrap\ImageDefinitionBootstrapItem;
use Ixocreate\Media\Package\Bootstrap\MediaBootstrapItem;

final class Package implements PackageInterface
{
    /**
     * @param ConfiguratorRegistryInterface $configuratorRegistry
     */
    public function configure(ConfiguratorRegistryInterface $configuratorRegistry): void
    {
    }

    /**
     * @param ServiceRegistryInterface $serviceRegistry
     */
    public function addServices(ServiceRegistryInterface $serviceRegistry): void
    {
    }

    /**
     * @return array|null
     */
    public function getConfigProvider(): ?array
    {
        return [];
    }

    /**
     * @param ServiceManagerInterface $serviceManager
     */
    public function boot(ServiceManagerInterface $serviceManager): void
    {
    }

    /**
     * @return null|string
     */
    public function getBootstrapDirectory(): ?string
    {
        return __DIR__ . '/../bootstrap';
    }

    /**
     * @return null|string
     */
    public function getConfigDirectory(): ?string
    {
        return __DIR__ . '/../config';
    }

    /**
     * @return array|null
     */
    public function getBootstrapItems(): ?array
    {
        return [
            DelegatorBootstrapItem::class,
            ImageDefinitionBootstrapItem::class,
            MediaBootstrapItem::class,
        ];
    }

    /**
     * @return array|null
     */
    public function getDependencies(): ?array
    {
        return null;
    }
}
