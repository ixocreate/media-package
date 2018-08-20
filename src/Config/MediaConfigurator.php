<?php
declare(strict_types=1);

namespace KiwiSuite\Media\Config;


use KiwiSuite\Contract\Application\ConfiguratorInterface;
use KiwiSuite\Contract\Application\ServiceRegistryInterface;

class MediaConfigurator implements ConfiguratorInterface
{
    private $whitelist = [
        'image' => [],
        'video' => [],
        'audio' => [],
        'text' => [],
        'application' => [],
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
     * Array needs to be defined as ['extension' => 'mimeType']
     */
    public function setImageWhiteliste(array $whitelist): void
    {
        $this->whitelist['image'] = $whitelist;
    }

    /**
     * @param array $whitelist
     * Array needs to be defined as ['extension' => 'mimeType']
     */
    public function setVideoWhitelist(array $whitelist): void
    {
        $this->whitelist['video'] = $whitelist;
    }

    /**
     * @param array $whitelist
     * Array needs to be defined as ['extension' => 'mimeType']
     */
    public function setAudioWhitelist(array $whitelist): void
    {
        $this->whitelist['audio'] = $whitelist;
    }

    /**
     * @param array $whitelist
     * Array needs to be defined as ['extension' => 'mimeType']
     */
    public function setTextWhitelist(array $whitelist): void
    {
        $this->whitelist['text'] = $whitelist;
    }

    /**
     * @param array $whitelist
     * Array needs to be defined as ['extension' => 'mimeType']
     */
    public function setApplicationWhitelist(array $whitelist): void
    {
        $this->whitelist['application'] = $whitelist;
    }
    
    public function registerService(ServiceRegistryInterface $serviceRegistry): void
    {
        $serviceRegistry->add(MediaProjectConfig::class, new MediaProjectConfig($this));
    }
}