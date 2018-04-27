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

    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    public function getMapping(): array
    {
        return $this->mapping;
    }

    public function serialize()
    {
        return \serialize($this->mapping);
    }

    public function unserialize($serialized)
    {
        $this->mapping = \unserialize($serialized);
    }
}