<?php
declare(strict_types=1);

namespace KiwiSuite\Media\Delegator;

use KiwiSuite\Contract\Application\SerializableServiceInterface;

final class DelegatorMapping implements SerializableServiceInterface
{
    /**
     * @var array
     */
    private $mapping;

    /**
     * DelegatorMapping constructor.
     * @param array $mapping
     */
    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * @return array
     */
    public function getMapping(): array
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