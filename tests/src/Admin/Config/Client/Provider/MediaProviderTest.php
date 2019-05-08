<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Test\Media\Admin\Config\Client\Provider;

use Ixocreate\Admin\UserInterface;
use Ixocreate\Media\Admin\Config\Client\Provider\MediaProvider;
use Ixocreate\Media\ImageDefinition\ImageDefinitionSubManager;
use Ixocreate\Media\ImageDefinitionInterface;
use Ixocreate\ServiceManager\ServiceManagerConfigInterface;
use Ixocreate\ServiceManager\ServiceManagerInterface;
use PHPUnit\Framework\TestCase;

class MediaProviderTest extends TestCase
{
    /**
     * @var ImageDefinitionSubManager
     */
    private $imageDefinitionSubManager;

    public function setUp()
    {
        /** @var ServiceManagerInterface $serviceManager */
        $serviceManager = $this->createMock(ServiceManagerInterface::class);
        /** @var ServiceManagerConfigInterface $serviceManagerConfig */
        $serviceManagerConfig = $this->createMock(ServiceManagerConfigInterface::class);

        $this->imageDefinitionSubManager = new ImageDefinitionSubManager($serviceManager, $serviceManagerConfig, ImageDefinitionInterface::class);
    }

    public function test__construct()
    {
        $mediaProvider = new MediaProvider($this->imageDefinitionSubManager);
        $this->assertInstanceOf(MediaProvider::class, $mediaProvider);
    }

    /**
     * @covers \Ixocreate\Media\Admin\Config\Client\Provider\MediaProvider::serviceName
     */
    public function testServiceName()
    {
        $mediaProvider = new MediaProvider($this->imageDefinitionSubManager);
        $this->assertSame('media', $mediaProvider::serviceName());
    }

    /**
     * @covers \Ixocreate\Media\Admin\Config\Client\Provider\MediaProvider::clientConfig
     */
    public function testClientConfig()
    {
        $mediaProvider = new MediaProvider($this->imageDefinitionSubManager);

        $this->assertEquals([], $mediaProvider->clientConfig($this->createMock(UserInterface::class)));

        $this->assertEquals([], $mediaProvider->clientConfig());
    }
}
