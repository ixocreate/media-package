<?php
/**
 * Created by PhpStorm.
 * User: afriedrich
 * Date: 10.08.18
 * Time: 10:08
 */

namespace KiwiSuite\Media\Config;


use KiwiSuite\Contract\Application\SerializableServiceInterface;

class MediaProjectConfig implements SerializableServiceInterface
{
    private $whitelist = [
        'image' => [],
        'video' => [],
        'audio' => [],
        'text' => [],
        'application' => [],
    ];
    
    private $publicStatus;

    /**
     * MediaProjectConfig constructor.
     * @param MediaConfigurator $mediaConfigurator
     */
    public function __construct(MediaConfigurator $mediaConfigurator)
    {
        $this->whitelist = $mediaConfigurator->whitelist();
        $this->publicStatus = $mediaConfigurator->publicStatus();
    }

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
     * @return array
     */
    public function imageWhitelist(): array
    {
        return $this->whitelist['image'];
    }

    /**
     * @return array
     */
    public function videoWhitelist(): array
    {
        return $this->whitelist['video'];
    }

    /**
     * @return array
     */
    public function audioWhitelist(): array
    {
        return $this->whitelist['audio'];
    }

    /**
     * @return array
     */
    public function textWhitelist(): array
    {
        return $this->whitelist['text'];
    }

    /**
     * @return array
     */
    public function applicationWhitelist(): array
    {
        return $this->whitelist['application'];
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize($this->whitelist);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $this->whitelist = unserialize($serialized);
    }
}