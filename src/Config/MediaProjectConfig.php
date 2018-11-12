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
        'global' => [],
        'document' => [],
    ];

    private $publicStatus;

    /**
     * MediaProjectConfig constructor.
     * @param MediaConfigurator $mediaConfigurator
     */
    public function __construct(MediaConfigurator $mediaConfigurator)
    {
        $this->whitelist = $mediaConfigurator->whitelist();

        $this->whitelist['image'] = \array_unique(\array_values($this->whitelist['image']));
        $this->whitelist['video'] = \array_unique(\array_values($this->whitelist['video']));
        $this->whitelist['video'] = \array_unique(\array_values($this->whitelist['video']));
        $this->whitelist['document'] = \array_unique(\array_values($this->whitelist['document']));

        $this->whitelist['global'] = \array_unique(
            \array_values(
                \array_merge(
                    $this->whitelist['global'],
                    $this->whitelist['image'],
                    $this->whitelist['video'],
                    $this->whitelist['audio'],
                    $this->whitelist['document']
                )
            )
        );

        $this->publicStatus = $mediaConfigurator->publicStatus();
    }

    /**
     * @return array
     */
    public function whitelist(): array
    {
        return $this->whitelist['global'];
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
    public function documentWhitelist(): array
    {
        return $this->whitelist['document'];
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return \serialize($this->whitelist);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $this->whitelist = \unserialize($serialized);
    }
}
