<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media;

use Ixocreate\Application\Configurator\ConfiguratorInterface;
use Ixocreate\Application\Service\ServiceRegistryInterface;
use Ixocreate\Media\Config\MediaPackageConfig;
use Zend\Diactoros\Uri;

class MediaConfigurator implements ConfiguratorInterface
{
    private $whitelist = [
        'image' => [],
        'video' => [],
        'audio' => [],
        'document' => [],
        'global' => [],
    ];

    /**
     * @var string
     */
    private $driver = '';

    /**
     * @var bool
     */
    private $publicStatus = false;

    /**
     * @var string
     */
    private $uri = '';

    /**
     * @return array
     */
    public function whitelist(): array
    {
        return $this->whitelist;
    }

    /**
     * @return bool
     */
    public function publicStatus(): bool
    {
        return $this->publicStatus;
    }

    /**
     * @return string
     */
    public function driver(): string
    {
        return $this->driver;
    }

    /**
     * @return string
     */
    public function uri(): string
    {
        return $this->uri;
    }

    /**
     * @param string $driver
     */
    public function setDriver(string $driver)
    {
        $this->driver = $driver;
    }

    /**
     * @param bool $bool
     */
    public function setPublicStatus(bool $bool): void
    {
        $this->publicStatus = $bool;
    }

    /**
     * @param string $uri
     */
    public function setUri(string $uri)
    {
        $uriObj = new Uri($uri);
        if (empty($uriObj->getHost())) {
            $uri = \ltrim($uri, '/');
            $uri = '/' . $uri;
        }

        $this->uri = $uri;
    }

    /**
     * @param array $whitelist
     */
    public function setImageWhitelist(array $whitelist): void
    {
        $this->whitelist['image'] = $whitelist;
    }

    /**
     * @param array $whitelist
     */
    public function setVideoWhitelist(array $whitelist): void
    {
        $this->whitelist['video'] = $whitelist;
    }

    /**
     * @param array $whitelist
     */
    public function setAudioWhitelist(array $whitelist): void
    {
        $this->whitelist['audio'] = $whitelist;
    }

    /**
     * @param array $whitelist
     */
    public function setGlobalWhitelist(array $whitelist): void
    {
        $this->whitelist['global'] = $whitelist;
    }

    /**
     * @param array $whitelist
     */
    public function setDocumentWhitelist(array $whitelist): void
    {
        $this->whitelist['document'] = $whitelist;
    }

    public function registerService(ServiceRegistryInterface $serviceRegistry): void
    {
        $serviceRegistry->add(MediaPackageConfig::class, new MediaPackageConfig($this));
    }
}
