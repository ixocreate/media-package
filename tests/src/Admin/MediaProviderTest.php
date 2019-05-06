<?php

namespace Ixocreate\Test\Media\Admin;

use Ixocreate\Admin\UserInterface;
use Ixocreate\Media\Admin\Config\Client\Provider\MediaProvider;
use Ixocreate\Media\ImageDefinition\ImageDefinitionSubManager;
use Ixocreate\ServiceManager\ServiceManagerConfigInterface;
use Ixocreate\ServiceManager\ServiceManagerInterface;
use PHPUnit\Framework\TestCase;

class MediaProviderTest extends TestCase
{

    private $imageDefinitionSubManager;

    public function setUp()
    {
        $serviceManagerInterface = $this->createMock(ServiceManagerInterface::class);
        $serviceManagerConfigInterface = $this->createMock(ServiceManagerConfigInterface::class);
        $validation = 'validation';
        $this->imageDefinitionSubManager = new ImageDefinitionSubManager($serviceManagerInterface, $serviceManagerConfigInterface, $validation);

    }

    public function test__construct()
    {
        $mediaProvider = new MediaProvider($this->imageDefinitionSubManager);
        $this->assertInstanceOf(MediaProvider::class, $mediaProvider);
    }

    public function testServiceName()
    {
        $mediaProvider = new MediaProvider($this->imageDefinitionSubManager);
        $this->assertSame('media', $mediaProvider::serviceName());
    }

    public function testClientConfig()
    {
        $mediaProvider = new MediaProvider($this->imageDefinitionSubManager);
        $this->assertEquals([], $mediaProvider->clientConfig($this->createMock(UserInterface::class)));
        $this->assertEquals([], $mediaProvider->clientConfig());
    }
}
