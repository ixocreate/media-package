<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Test\Media;

use Ixocreate\Application\Service\ServiceRegistryInterface;
use Ixocreate\Media\MediaConfigurator;
use PHPUnit\Framework\TestCase;

class MediaConfiguratorTest extends TestCase
{
    /**
     * @var MediaConfigurator
     */
    private $mediaConfigurator;

    public function setUp(): void
    {
        $this->mediaConfigurator = new MediaConfigurator();
    }

    /**
     * @covers \Ixocreate\Media\MediaConfigurator
     */
    public function testMediaConfigurator()
    {
        $driver = 'driver';
        $this->mediaConfigurator->setDriver($driver);
        $this->assertSame($this->mediaConfigurator->driver(), $driver);

        $publicStatus = true;
        $this->mediaConfigurator->setPublicStatus($publicStatus);
        $this->assertSame($this->mediaConfigurator->publicStatus(), $publicStatus);

        $uri = 'uri';
        $this->mediaConfigurator->setUri($uri);
        $this->assertStringMatchesFormat($this->mediaConfigurator->uri(), '/uri');

        $uri = '/uri';
        $this->mediaConfigurator->setUri($uri);
        $this->assertSame($this->mediaConfigurator->uri(), $uri);

        $image = [];
        $this->mediaConfigurator->setImageWhitelist($image);
        $this->assertSame($this->mediaConfigurator->whitelist()['image'], $image);

        $video = [];
        $this->mediaConfigurator->setVideoWhitelist($video);
        $this->assertSame($this->mediaConfigurator->whitelist()['video'], $video);

        $audio = [];
        $this->mediaConfigurator->setAudioWhitelist($audio);
        $this->assertSame($this->mediaConfigurator->whitelist()['audio'], $audio);

        $document = [];
        $this->mediaConfigurator->setDocumentWhitelist($document);
        $this->assertSame($this->mediaConfigurator->whitelist()['document'], $document);

        $global = [];
        $this->mediaConfigurator->setGlobalWhitelist($global);
        $this->assertSame($this->mediaConfigurator->whitelist()['global'], $global);

        $parallelImageProcessing = true;
        $this->mediaConfigurator->setParallelImageProcessing($parallelImageProcessing);
        $this->assertSame($this->mediaConfigurator->isParallelImageProcessing(), $parallelImageProcessing);

        $collector = [];
        $serviceRegistry = $this->createMock(ServiceRegistryInterface::class);
        $serviceRegistry->method('add')->willReturnCallback(function ($name, $object) use (&$collector) {
            $collector[$name] = $object;
        });

        $this->mediaConfigurator->registerService($serviceRegistry);
    }
}
