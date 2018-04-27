<?php
declare(strict_types=1);

namespace KiwiSuite\Media\ImageDefinition;

use KiwiSuite\Contract\Application\ConfiguratorInterface;
use KiwiSuite\Contract\Application\ServiceRegistryInterface;
use KiwiSuite\ServiceManager\SubManager\SubManagerConfigurator;

final class ImageDefinitionConfigurator implements ConfiguratorInterface
{
    private $subManagerConfigurator;

    public function __construct()
    {
        $this->subManagerConfigurator = new SubManagerConfigurator(ImageDefinitionSubManager::class, ImageDefinitionInterface::class);
    }

    public function getManagerConfigurator()
    {
        return $this->subManagerConfigurator;
    }

    public function getImageDefinitionMapping()
    {
        $config = $this->subManagerConfigurator;

        $factories = $config->getServiceManagerConfig()->getFactories();

        $imageDefinitionMapping = [];
        foreach ($factories as $id => $factory) {
            if (!\is_subclass_of($id, ImageDefinitionInterface::class, true)) {
                throw new \InvalidArgumentException(\sprintf("'%s' doesn't implement '%s'", $id, ImageDefinitionInterface::class));
            }
            $name = \forward_static_call([$id, 'name']);
            $imageDefinitionMapping[$name] = $id;
        }

        return new ImageDefinitionMapping($imageDefinitionMapping);
    }

    public function registerService(ServiceRegistryInterface $serviceRegistry): void
    {
        $serviceRegistry->add(ImageDefinitionMapping::class, $this->getImageDefinitionMapping());
        $this->subManagerConfigurator->registerService($serviceRegistry);
    }
}