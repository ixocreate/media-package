<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media;

use Ixocreate\Application\PackageInterface;
use Ixocreate\Application\Service\Configurator\ConfiguratorRegistryInterface;
use Ixocreate\Application\Service\Registry\ServiceRegistryInterface;
use Ixocreate\Media\Bootstrap\MediaBootstrapItem;
use Ixocreate\Media\Bootstrap\MediaDefinitionBootstrapItem;
use Ixocreate\Media\Bootstrap\MediaDelegatorBootstrapItem;
use Ixocreate\ServiceManager\ServiceManagerInterface;

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
            MediaDelegatorBootstrapItem::class,
            MediaDefinitionBootstrapItem::class,
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
