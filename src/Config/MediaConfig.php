<?php
/**
 * kiwi-suite/media (https://github.com/kiwi-suite/media)
 *
 * @package kiwi-suite/media
 * @see https://github.com/kiwi-suite/media
 * @copyright Copyright (c) 2010 - 2018 kiwi suite GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace KiwiSuite\Media\Config;

use KiwiSuite\Media\Exception\InvalidExtensionException;
use KiwiSuite\Media\Exception\InvalidConfigException;
use Psr\Http\Message\UriInterface;

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
     * MediaConfig constructor.
     * @param string $driver
     * @param UriInterface $uri
     * @param MediaProjectConfig $mediaProjectConfig
     */
    public function __construct(string $driver, UriInterface $uri, MediaProjectConfig $mediaProjectConfig)
    {
        $this->driver = $driver;
        $this->uri = $uri;
        $this->mediaProjectConfig = $mediaProjectConfig;
        $this->assertDriver();
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

    /**
     * @throws \KiwiSuite\Media\Exception\InvalidExtensionException
     * @throws \KiwiSuite\Media\Exception\InvalidConfigException
     */
    private function assertDriver(): void
    {
        $allowedConfigParameter = ['automatic', 'gd', 'imagick'];

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
