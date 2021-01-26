<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Test\Media\Config;

use Ixocreate\Media\Config\MediaPackageConfig;
use Ixocreate\Media\MediaConfigurator;
use PHPUnit\Framework\TestCase;

class MediaPackageConfigTest extends TestCase
{
    /**
     * @var MediaPackageConfig
     */
    private $mediaPackageConfig;

    /**
     * @var MediaConfigurator
     */
    private $mediaConfigurator;

    public function setUp(): void
    {
        $this->mediaConfigurator = new MediaConfigurator();
        $this->mediaConfigurator->setGlobalWhitelist(['farBoo']);
        $this->mediaConfigurator->setImageWhitelist(['foo']);
        $this->mediaConfigurator->setVideoWhitelist(['bar']);
        $this->mediaConfigurator->setAudioWhitelist(['fooBar']);
        $this->mediaConfigurator->setDocumentWhitelist(['barFoo']);
        $this->mediaConfigurator->setPublicStatus(true);
        $this->mediaConfigurator->setDriver('automatic');
        $this->mediaConfigurator->setUri('/foo');

        $this->mediaPackageConfig = new MediaPackageConfig($this->mediaConfigurator);
    }

    public function testImageWhitelist()
    {
        $this->assertSame(['foo'], $this->mediaPackageConfig->imageWhitelist());
    }

    public function testVideoWhitelist()
    {
        $this->assertSame(['bar'], $this->mediaPackageConfig->videoWhitelist());
    }

    public function testAudioWhitelist()
    {
        $this->assertSame(['fooBar'], $this->mediaPackageConfig->audioWhitelist());
    }

    public function testDocumentWhitelist()
    {
        $this->assertSame(['barFoo'], $this->mediaPackageConfig->documentWhitelist());
    }

    public function testPublicStatus()
    {
        $this->assertSame(true, $this->mediaPackageConfig->publicStatus());
    }

    public function testDriver()
    {
        $this->assertSame('automatic', $this->mediaPackageConfig->driver());
    }

    public function testUri()
    {
        $this->assertSame('/foo', $this->mediaPackageConfig->uri());
    }

    public function testWhitelist()
    {
        $expected =
            \array_unique(
                \array_values(
                    \array_merge(
                        $this->mediaPackageConfig->whitelist(),
                        $this->mediaPackageConfig->imageWhitelist(),
                        $this->mediaPackageConfig->videoWhitelist(),
                        $this->mediaPackageConfig->audioWhitelist(),
                        $this->mediaPackageConfig->documentWhitelist()
                    )
                )
            );
        $this->assertSame($expected, $this->mediaPackageConfig->whitelist());
    }

    public function testSerialize()
    {
        $whitelist['image'] = $this->mediaPackageConfig->imageWhitelist();
        $whitelist['video'] = $this->mediaPackageConfig->videoWhitelist();
        $whitelist['audio'] = $this->mediaPackageConfig->audioWhitelist();
        $whitelist['document'] = $this->mediaPackageConfig->documentWhitelist();
        $whitelist['global'] = $this->mediaPackageConfig->whitelist();

        $serialize = \serialize([
            'whitelist' => $whitelist,
            'publicStatus' => $this->mediaPackageConfig->publicStatus(),
            'driver' => $this->mediaPackageConfig->driver(),
            'uri' => $this->mediaPackageConfig->uri(),
            'parallelImageProcessing' => $this->mediaPackageConfig->isParallelImageProcessing(),
        ]);

        $this->assertSame($serialize, $this->mediaPackageConfig->serialize());
    }

    public function testUnserialize()
    {
        $whitelist['image'] = $this->mediaPackageConfig->imageWhitelist();
        $whitelist['video'] = $this->mediaPackageConfig->videoWhitelist();
        $whitelist['audio'] = $this->mediaPackageConfig->audioWhitelist();
        $whitelist['document'] = $this->mediaPackageConfig->documentWhitelist();
        $whitelist['global'] = $this->mediaPackageConfig->whitelist();

        $serialize = \serialize([
            'whitelist' => $whitelist,
            'publicStatus' => $this->mediaPackageConfig->publicStatus(),
            'driver' => $this->mediaPackageConfig->driver(),
            'uri' => $this->mediaPackageConfig->uri(),
            'parallelImageProcessing' => $this->mediaPackageConfig->isParallelImageProcessing(),
        ]);

        $expected = \unserialize($serialize);

        $this->mediaPackageConfig->unserialize($serialize);

        $unserialize = [
            'whitelist' => [
                'image' => $this->mediaPackageConfig->imageWhitelist(),
                'video' => $this->mediaPackageConfig->videoWhitelist(),
                'audio' => $this->mediaPackageConfig->audioWhitelist(),
                'document' => $this->mediaPackageConfig->documentWhitelist(),
                'global' => $this->mediaPackageConfig->whitelist(),
            ],
            'publicStatus' => $this->mediaPackageConfig->publicStatus(),
            'driver' => $this->mediaPackageConfig->driver(),
            'uri' => $this->mediaPackageConfig->uri(),
            'parallelImageProcessing' => $this->mediaPackageConfig->isParallelImageProcessing(),
        ];

        $this->assertSame($expected, $unserialize);
    }
}
