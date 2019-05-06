<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Test\Media\Admin;

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
        $serviceManagerInterface = $this->createMock(ServiceManagerInterface::class);
        $serviceManagerConfigInterface = $this->createMock(ServiceManagerConfigInterface::class);
        $this->imageDefinitionSubManager = new ImageDefinitionSubManager($serviceManagerInterface, $serviceManagerConfigInterface, ImageDefinitionInterface::class);
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
