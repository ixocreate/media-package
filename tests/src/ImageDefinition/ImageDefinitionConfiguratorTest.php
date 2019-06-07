<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Test\Media\ImageDefinition;

use Ixocreate\Application\Service\AbstractServiceManagerConfigurator;
use Ixocreate\Application\Service\ServiceManagerConfig;
use Ixocreate\Application\Service\ServiceRegistryInterface;
use Ixocreate\Media\ImageDefinition\ImageDefinitionConfigurator;
use Ixocreate\Media\ImageDefinition\ImageDefinitionSubManager;
use Ixocreate\Media\ImageDefinition\ImageDefinitionInterface;
use Ixocreate\Misc\Media\ImageDefinitionMock;
use PHPUnit\Framework\TestCase;

class ImageDefinitionConfiguratorTest extends TestCase
{
    /**
     * @var ImageDefinitionConfigurator
     */
    private $imageDefinitionConfigurator;

    public function setUp()
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

        $this->assertSame('/foo', $configurator->getDirectories()[0]['dir']);
        $this->assertSame(true, $configurator->getDirectories()[0]['recursive']);
        $this->assertSame(ImageDefinitionInterface::class, $configurator->getDirectories()[0]['only'][0]);
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
        $this->assertInstanceOf(ServiceManagerConfig::class, $collector[ImageDefinitionSubManager::class . '::Config']);
    }
}
