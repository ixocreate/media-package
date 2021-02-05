<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Test\Media\ImageDefinition;

use Ixocreate\Application\Service\ServiceRegistryInterface;
use Ixocreate\Application\ServiceManager\AbstractServiceManagerConfigurator;
use Ixocreate\Application\ServiceManager\SubManagerConfig;
use Ixocreate\Media\ImageDefinition\ImageDefinitionConfigurator;
use Ixocreate\Media\ImageDefinition\ImageDefinitionInterface;
use Ixocreate\Media\ImageDefinition\ImageDefinitionSubManager;
use Ixocreate\Misc\Media\ImageDefinitionMock;
use PHPUnit\Framework\TestCase;

class ImageDefinitionConfiguratorTest extends TestCase
{
    /**
     * @var ImageDefinitionConfigurator
     */
    private $imageDefinitionConfigurator;

    public function setUp(): void
    {
        $this->imageDefinitionConfigurator = new ImageDefinitionConfigurator();
    }

    public function testGetManagerConfigurator()
    {
        $this->assertInstanceOf(AbstractServiceManagerConfigurator::class, $this->imageDefinitionConfigurator->getManagerConfigurator());
    }

    public function testAddDirectory()
    {
        $this->imageDefinitionConfigurator->addDirectory('/foo', true);
        $configurator = $this->imageDefinitionConfigurator->getManagerConfigurator();

        $directories = $configurator->getDirectories();
        $this->assertArrayHasKey('/foo/', $directories);
        $this->assertSame('/foo/', $directories['/foo/']['dir']);
        $this->assertSame(true, $directories['/foo/']['recursive']);
        $this->assertSame(ImageDefinitionInterface::class, $directories['/foo/']['only'][0]);
    }

    public function testAddImageDefinition()
    {
        $this->imageDefinitionConfigurator->addImageDefinition(ImageDefinitionMock::class);
        $configurator = $this->imageDefinitionConfigurator->getManagerConfigurator();
        $this->assertArrayHasKey(ImageDefinitionMock::class, $configurator->getFactories());
    }

    public function testRegisterService()
    {
        $collector = [];
        $serviceRegistry = $this->createMock(ServiceRegistryInterface::class);
        $serviceRegistry->method('add')->willReturnCallback(function ($name, $object) use (&$collector) {
            $collector[$name] = $object;
        });

        $this->imageDefinitionConfigurator->registerService($serviceRegistry);

        $this->assertArrayHasKey(ImageDefinitionSubManager::class . '::Config', $collector);
        $this->assertInstanceOf(SubManagerConfig::class, $collector[ImageDefinitionSubManager::class . '::Config']);
    }
}
