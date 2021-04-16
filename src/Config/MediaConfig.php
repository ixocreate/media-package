<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Config;

use Ixocreate\Application\Uri\ApplicationUri;
use Ixocreate\Media\Exception\InvalidConfigException;
use Ixocreate\Media\Exception\InvalidExtensionException;
use Laminas\Diactoros\Uri;
use Psr\Http\Message\UriInterface;

final class MediaConfig
{
    /**
     * @var string
     */
    private $driver;

    /**
     * @var MediaPackageConfig
     */
    private $mediaPackageConfig;

    /**
     * @var UriInterface
     */
    private $uri;

    /**
     * @var ApplicationUri
     */
    private $projectUri;

    /**
     * MediaConfig constructor.
     *
     * @param MediaPackageConfig $mediaPackageConfig
     * @param ApplicationUri $projectUri
     */
    public function __construct(MediaPackageConfig $mediaPackageConfig, ApplicationUri $projectUri)
    {
        $this->mediaPackageConfig = $mediaPackageConfig;
        $this->projectUri = $projectUri;
        $this->assertDriver();
        $this->assertUri();
    }

    /**
     * @return UriInterface
     * @deprecated
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * @return string
     * @deprecated
     */
    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     * @return UriInterface
     */
    public function uri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * @return string
     */
    public function driver(): string
    {
        return $this->driver;
    }

    /**
     * @return array
     */
    public function whitelist(): array
    {
        return $this->mediaPackageConfig->whitelist();
    }

    /**
     * @return bool
     */
    public function publicStatus(): bool
    {
        return $this->mediaPackageConfig->publicStatus();
    }

    /**
     * @return array
     */
    public function imageWhitelist(): array
    {
        return $this->mediaPackageConfig->imageWhitelist();
    }

    /**
     * @return array
     */
    public function videoWhitelist(): array
    {
        return $this->mediaPackageConfig->videoWhitelist();
    }

    /**
     * @return array
     */
    public function audioWhitelist(): array
    {
        return $this->mediaPackageConfig->audioWhitelist();
    }

    /**
     * @return array
     */
    public function documentWhitelist(): array
    {
        return $this->mediaPackageConfig->documentWhitelist();
    }

    /**
     * @return bool
     */
    public function isParallelImageProcessing(): bool
    {
        return $this->mediaPackageConfig->isParallelImageProcessing();
    }

    /**
     * @return bool
     */
    public function generateWebP(): bool
    {
        return $this->mediaPackageConfig->generateWebP();
    }

    private function assertUri(): void
    {
        $uri = new Uri($this->mediaPackageConfig->uri());
        if (empty($uri->getHost())) {
            $projectUri = $this->projectUri;

            $uri = $uri->withPath(\rtrim($projectUri->getMainUri()->getPath(), '/') . '/' . \ltrim($uri->getPath(), '/'));
            $uri = $uri->withHost($projectUri->getMainUri()->getHost());
            $uri = $uri->withScheme($projectUri->getMainUri()->getScheme());
            $uri = $uri->withPort($projectUri->getMainUri()->getPort());
        }
        $this->uri = $uri;
    }

    /**
     * @throws \Ixocreate\Media\Exception\InvalidExtensionException
     * @throws \Ixocreate\Media\Exception\InvalidConfigException
     */
    private function assertDriver(): void
    {
        $allowedConfigParameter = ['automatic', 'gd', 'imagick'];

        $this->driver = $this->mediaPackageConfig->driver();

        if (empty($driver)) {
            $this->driver = 'automatic';
        }
        // @codeCoverageIgnoreStart
        if (\in_array($this->driver, $allowedConfigParameter)) {
            switch ($this->driver) {
                case 'gd':
                    if (\extension_loaded('gd') === false) {
                        throw new InvalidExtensionException("PHP Extension 'gd' could not be found");
                    }
                    break;
                case 'imagick':
                    if (\extension_loaded('imagick') === false) {
                        throw new InvalidExtensionException("PHP Extension 'imagick' could not be found");
                    }
                    break;
                case 'automatic':
                    $this->driver = 'imagick';
                    if (\extension_loaded('imagick') === false) {
                        $this->driver = 'gd';
                        if (\extension_loaded('gd') === false) {
                            throw new InvalidExtensionException("Neither 'gd' or 'imagick' PHP Extension could be found");
                        }
                    }
                    break;
            }
        } else {
            throw new InvalidConfigException(\sprintf("Given media config driver: '%s', is not valid", $this->driver));
        }
        // @codeCoverageIgnoreEnd
    }
}
