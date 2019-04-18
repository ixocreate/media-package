<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Package\Media\ImageDefinition;

use Ixocreate\Application\ConfiguratorInterface;
use Ixocreate\Application\ServiceRegistryInterface;
use Ixocreate\Package\Media\ImageDefinitionInterface;
use Ixocreate\ServiceManager\Factory\AutowireFactory;
use Ixocreate\ServiceManager\SubManager\SubManagerConfigurator;

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
        $this->subManagerConfigurator = new SubManagerConfigurator(ImageDefinitionSubManager::class, ImageDefinitionInterface::class);
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
