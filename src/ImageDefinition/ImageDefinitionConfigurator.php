<?php
declare(strict_types=1);

namespace KiwiSuite\Media\ImageDefinition;

use KiwiSuite\Contract\Application\ConfiguratorInterface;
use KiwiSuite\Contract\Application\ServiceRegistryInterface;
use KiwiSuite\ServiceManager\Factory\AutowireFactory;
use KiwiSuite\ServiceManager\SubManager\SubManagerConfigurator;

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
     * @return ImageDefinitionMapping
     */
    public function getImageDefinitionMapping()
    {
        $config = $this->subManagerConfigurator;

        $factories = $config->getServiceManagerConfig()->getFactories();

        $imageDefinitionMapping = [];
        foreach ($factories as $id => $factory) {
            if (!\is_subclass_of($id, ImageDefinitionInterface::class, true)) {
                throw new \InvalidArgumentException(\sprintf("'%s' doesn't implement '%s'", $id, ImageDefinitionInterface::class));
            }
            $name = \forward_static_call([$id, 'getName']);
            $imageDefinitionMapping[$name] = $id;
        }

        return new ImageDefinitionMapping($imageDefinitionMapping);
    }

    /**
     * @param ServiceRegistryInterface $serviceRegistry
     */
    public function registerService(ServiceRegistryInterface $serviceRegistry): void
    {
        $serviceRegistry->add(ImageDefinitionMapping::class, $this->getImageDefinitionMapping());
        $this->subManagerConfigurator->registerService($serviceRegistry);
    }
}