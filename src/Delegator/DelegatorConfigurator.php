<?php
declare(strict_types=1);

namespace KiwiSuite\Media\Delegator;

use KiwiSuite\Media\Delegator\DelegatorInterface;
use KiwiSuite\Contract\Application\ConfiguratorInterface;
use KiwiSuite\Contract\Application\ServiceRegistryInterface;
use KiwiSuite\Media\Delegator\DelegatorMapping;
use KiwiSuite\ServiceManager\Factory\AutowireFactory;
use KiwiSuite\ServiceManager\SubManager\SubManagerConfigurator;

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
     * @return DelegatorMapping
     */
    public function getDelegatorMapping() : DelegatorMapping
    {
        $config = $this->getManagerConfigurator();

        $factories = $config->getServiceManagerConfig()->getFactories();

        $delegatorMapping = [];
        foreach ($factories as $id => $factory) {
            if (!\is_subclass_of($id, DelegatorInterface::class, true)) {
                throw new \InvalidArgumentException(\sprintf("'%s' doesn't implement '%s'", $id, DelegatorInterface::class));
            }

            $name = \forward_static_call([$id, 'getName']);
            $delegatorMapping[$name] = $id;
        }

        return new DelegatorMapping($delegatorMapping);
    }

    /**
     * @param ServiceRegistryInterface $serviceRegistry
     */
    public function registerService(ServiceRegistryInterface $serviceRegistry): void
    {
        $serviceRegistry->add(DelegatorMapping::class, $this->getDelegatorMapping());
        $this->subManagerConfigurator->registerService($serviceRegistry);
    }
}