<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Test\Media\Config;

use Ixocreate\Application\Uri\ApplicationUri;
use Ixocreate\Application\Uri\ApplicationUriConfigurator;
use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\Config\MediaPackageConfig;
use Ixocreate\Media\MediaConfigurator;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

class MediaConfigTest extends TestCase
{
    /**
     * @var MediaConfig
     */
    private $mediaConfig;

    public function setUp(): void
    {
        $mediaConfigurator = new MediaConfigurator();

        $mediaConfigurator->setImageWhitelist(['foo']);
        $mediaConfigurator->setVideoWhitelist(['bar']);
        $mediaConfigurator->setAudioWhitelist(['fooBar']);
        $mediaConfigurator->setDocumentWhitelist(['barFoo']);
        $mediaConfigurator->setPublicStatus(true);

        $mediaPackageConfig = new MediaPackageConfig($mediaConfigurator);

        $applicationUriConfigurator = new ApplicationUriConfigurator();

        $applicationUri = new ApplicationUri($applicationUriConfigurator);


        $this->mediaConfig = new MediaConfig($mediaPackageConfig, $applicationUri);
    }

    public function testGetUri()
    {
        $this->assertInstanceOf(UriInterface::class, $this->mediaConfig->getUri());
    }

    public function testGetDriver()
    {
        $this->assertIsString($this->mediaConfig->getDriver());
    }

    public function testUri()
    {
        $this->assertInstanceOf(UriInterface::class, $this->mediaConfig->uri());
    }

    public function testDriver()
    {
        $this->assertIsString($this->mediaConfig->driver());
    }

    public function testWhitelist()
    {
        $expected =
            \array_unique(
                \array_values(
                    \array_merge(
                        $this->mediaConfig->imageWhitelist(),
                        $this->mediaConfig->videoWhitelist(),
                        $this->mediaConfig->audioWhitelist(),
                        $this->mediaConfig->documentWhitelist()
                    )
                )
            );

        $this->assertSame($expected, $this->mediaConfig->whitelist());
    }

    public function testPublicStatus()
    {
        $this->assertSame(true, $this->mediaConfig->publicStatus());
    }

    public function testImageWhitelist()
    {
        $this->assertSame(['foo'], $this->mediaConfig->imageWhitelist());
    }

    public function testVideoWhitelist()
    {
        $this->assertSame(['bar'], $this->mediaConfig->videoWhitelist());
    }

    public function testAudioWhitelist()
    {
        $this->assertSame(['fooBar'], $this->mediaConfig->audioWhitelist());
    }

    public function testDocumentWhitelist()
    {
        $this->assertSame(['barFoo'], $this->mediaConfig->documentWhitelist());
    }
}
