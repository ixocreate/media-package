<?php
/**
 * @see https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Config;

use Ixocreate\Contract\Application\ConfiguratorInterface;
use Ixocreate\Contract\Application\ServiceRegistryInterface;

class MediaConfigurator implements ConfiguratorInterface
{
    private $whitelist = [
        'image' => [],
        'video' => [],
        'audio' => [],
        'document' => [],
        'global' => [],
    ];

    private $publicStatus = false;

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

    public function setPublicStatus(bool $bool): void
    {
        $this->publicStatus = $bool;
    }

    /**
     * @param array $whitelist
     */
    public function setImageWhiteliste(array $whitelist): void
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
        $serviceRegistry->add(MediaProjectConfig::class, new MediaProjectConfig($this));
    }
}
