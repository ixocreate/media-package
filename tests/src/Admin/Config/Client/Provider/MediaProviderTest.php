<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Test\Media\Admin\Config\Client\Provider;

use Ixocreate\Admin\UserInterface;
use Ixocreate\Application\Service\ServiceManagerConfig;
use Ixocreate\Application\Service\ServiceManagerConfigurator;
use Ixocreate\Media\Admin\Config\Client\Provider\MediaProvider;
use Ixocreate\Media\ImageDefinition\ImageDefinitionSubManager;
use Ixocreate\Media\ImageDefinitionInterface;
use Ixocreate\Misc\Media\ImageDefinitionMock;
use Ixocreate\ServiceManager\ServiceManager;
use Ixocreate\ServiceManager\ServiceManagerSetup;
use PHPUnit\Framework\TestCase;

class MediaProviderTest extends TestCase
{
    /**
     * @var ImageDefinitionSubManager
     */
    private $imageDefinitionSubManager;

    /**
     * @var MediaProvider
     */
    private $mediaProvider;

    public function setUp()
    {
        $imageDefinitionConfigurator = new ServiceManagerConfigurator();
        $imageDefinitionConfigurator->addFactory(ImageDefinitionMock::class);

        $this->imageDefinitionSubManager = new ImageDefinitionSubManager(
            new ServiceManager(new ServiceManagerConfig(new ServiceManagerConfigurator()), new ServiceManagerSetup()),
            new ServiceManagerConfig($imageDefinitionConfigurator),
            ImageDefinitionInterface::class
        );

        $this->mediaProvider = new MediaProvider($this->imageDefinitionSubManager);
    }

    public function test__construct()
    {
        $this->assertInstanceOf(MediaProvider::class, $this->mediaProvider);
    }

    /**
     * @covers \Ixocreate\Media\Admin\Config\Client\Provider\MediaProvider::serviceName
     */
    public function testServiceName()
    {
        $this->assertSame('media', $this->mediaProvider::serviceName());
    }

    /**
     * @covers \Ixocreate\Media\Admin\Config\Client\Provider\MediaProvider::clientConfig
     */
    public function testClientConfig()
    {
        $imageDefinition = $this->imageDefinitionSubManager->get('foo');

        $compare = [
            0 => [
                'name' => $imageDefinition::serviceName(),
                'label' => \ucfirst($imageDefinition::serviceName()),
                'width' => $imageDefinition->width(),
                'height' => $imageDefinition->height(),
                'upscale' => $imageDefinition->upscale(),
                'mode' => $imageDefinition->mode(),
            ]
        ];

        $this->assertEquals($compare, $this->mediaProvider->clientConfig($this->createMock(UserInterface::class)));

        $this->assertEquals([], $this->mediaProvider->clientConfig());
    }
}
