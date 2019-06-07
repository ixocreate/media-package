<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\ImageDefinition;

use Ixocreate\Application\Configurator\ConfiguratorInterface;
use Ixocreate\Application\Service\AbstractServiceManagerConfigurator;
use Ixocreate\Application\Service\ServiceRegistryInterface;
use Ixocreate\Application\Service\SubManagerConfigurator;
use Ixocreate\Media\ImageDefinition\ImageDefinitionInterface;
use Ixocreate\ServiceManager\Factory\AutowireFactory;

final class ImageDefinitionConfigurator implements ConfiguratorInterface
{
    /**
     * @var SubManagerConfigurator
     */
    private $subManagerConfigurator;

    /**
     * ImageDefinitionConfigurator constructor.
     */
    public function __construct()
    {
        $this->subManagerConfigurator = new SubManagerConfigurator(
            ImageDefinitionSubManager::class,
            ImageDefinitionInterface::class
        );
    }

    /**
     * @return AbstractServiceManagerConfigurator
     */
    public function getManagerConfigurator(): AbstractServiceManagerConfigurator
    {
        return $this->subManagerConfigurator;
    }

    /**
     * @param string $directory
     * @param bool $recursive
     */
    public function addDirectory(string $directory, bool $recursive)
    {
        $this->subManagerConfigurator->addDirectory($directory, $recursive);
    }

    /**
     * @param string $action
     * @param string $factory
     */
    public function addImageDefinition(string $action, string $factory = AutowireFactory::class)
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
