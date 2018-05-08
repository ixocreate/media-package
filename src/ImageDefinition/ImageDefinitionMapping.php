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

namespace KiwiSuite\Media\ImageDefinition;

use KiwiSuite\Contract\Application\SerializableServiceInterface;

final class ImageDefinitionMapping implements SerializableServiceInterface
{
    /**
     * @var array
     */
    private $mapping;

    /**
     * ImageDefinitionMapping constructor.
     * @param array $mapping
     */
    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * @return array
     */
    public function getMapping() : array
    {
        return $this->mapping;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return \serialize($this->mapping);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $this->mapping = \unserialize($serialized);
    }
}