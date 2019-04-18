<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Package\Config;

use Ixocreate\Media\Package\Exception\InvalidExtensionException;
use Ixocreate\Media\Package\Exception\InvalidConfigException;
use Ixocreate\Application\Uri\ApplicationUri;
use Psr\Http\Message\UriInterface;
use Zend\Diactoros\Uri;

final class MediaConfig
{
    /**
     * @var string
     */
    private $driver;

    /**
     * @var MediaProjectConfig
     */
    private $mediaProjectConfig;

    /**
     * @var UriInterface
     */
    private $uri;

    /**
     * @var Uri
     */
    private $projectUri;

    /**
     * MediaConfig constructor.
     *
     * @param MediaProjectConfig $mediaProjectConfig
     * @param Uri $projectUri
     */
    public function __construct(MediaProjectConfig $mediaProjectConfig, Uri $projectUri)
    {
        $this->mediaProjectConfig = $mediaProjectConfig;
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
        return $this->mediaProjectConfig->whitelist();
    }

    /**
     * @return bool
     */
    public function publicStatus(): bool
    {
        return $this->mediaProjectConfig->publicStatus();
    }

    /**
     * @return array
     */
    public function imageWhitelist(): array
    {
        return $this->mediaProjectConfig->imageWhitelist();
    }

    /**
     * @return array
     */
    public function videoWhitelist(): array
    {
        return $this->mediaProjectConfig->videoWhitelist();
    }

    /**
     * @return array
     */
    public function audioWhitelist(): array
    {
        return $this->mediaProjectConfig->audioWhitelist();
    }

    /**
     * @return array
     */
    public function documentWhitelist(): array
    {
        return $this->mediaProjectConfig->documentWhitelist();
    }

    private function assertUri(): void
    {
        $uri = new Uri($this->mediaProjectConfig->uri());
        if (empty($uri->getHost())) {
            /** @var Uri $projectUri */
            $projectUri = $this->projectUri;

            $uri = $uri->withPath(\rtrim($projectUri->getMainUri()->getPath(), '/') . '/' . \ltrim($uri->getPath(), '/'));
            $uri = $uri->withHost($projectUri->getMainUri()->getHost());
            $uri = $uri->withScheme($projectUri->getMainUri()->getScheme());
            $uri = $uri->withPort($projectUri->getMainUri()->getPort());
        }
        $this->uri = $uri;
    }

    /**
     * @throws \Ixocreate\Media\Package\Exception\InvalidExtensionException
     * @throws \Ixocreate\Media\Package\Exception\InvalidConfigException
     */
    private function assertDriver(): void
    {
        $allowedConfigParameter = ['automatic', 'gd', 'imagick'];

        $this->driver = $this->mediaProjectConfig->driver();

        if (empty($driver)) {
            $this->driver = 'automatic';
        }
        if (\in_array($this->driver, $allowedConfigParameter)) {
            switch ($this->driver):
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
            endswitch;
        } else {
            throw new InvalidConfigException(\sprintf("Given media config driver: '%s', is not valid", $this->driver));
        }
    }
}
