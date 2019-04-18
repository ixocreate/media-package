<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Delegator;

use Ixocreate\Application\Service\Configurator\ConfiguratorInterface;
use Ixocreate\Application\Service\Registry\ServiceRegistryInterface;
use Ixocreate\Media\DelegatorInterface;
use Ixocreate\ServiceManager\Factory\AutowireFactory;
use Ixocreate\Application\Service\SubManagerConfigurator;

final class DelegatorConfigurator implements ConfiguratorInterface
{
    /**
     * @var SubManagerConfigurator
     */
    private $subManagerConfigurator;

    /**
     * DelegatorConfigurator constructor.
     */
    public function __construct()
    {
        $this->subManagerConfigurator = new SubManagerConfigurator(DelegatorSubManager::class, DelegatorInterface::class);
    }

    /**
     * @return SubManagerConfigurator
     */
    public function getManagerConfigurator()
    {
        return $this->subManagerConfigurator;
    }

    /**
     * @param string $directory
     * @param bool $recursive
     */
    public function addDirectory(string $directory, bool $recursive = true): void
    {
        $this->subManagerConfigurator->addDirectory($directory, $recursive);
    }

    /**
     * @param string $action
     * @param string $factory
     */
    public function addDelegator(string $action, string $factory = AutowireFactory::class)
    {
        $this->subManagerConfigurator->addFactory($action, $factory);
    }

    /**
     * @param ServiceRegistryInterface $serviceRegistry
     */
    public function registerService(ServiceRegistryInterface $serviceRegistry): void
    {
        $this->subManagerConfigurator->registerService($serviceRegistry);
    }
}
