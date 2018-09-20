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

namespace KiwiSuite\Media\Entity;

use KiwiSuite\Contract\Type\TypeInterface;
use KiwiSuite\Entity\Entity\Definition;
use KiwiSuite\Entity\Entity\DefinitionCollection;
use KiwiSuite\Entity\Entity\EntityInterface;
use KiwiSuite\Entity\Entity\EntityTrait;
use KiwiSuite\CommonTypes\Entity\UuidType;
use KiwiSuite\CommonTypes\Entity\DateTimeType;

final class Media implements EntityInterface
{
    use EntityTrait;

    private $id;
    private $basePath;
    private $filename;
    private $mimeType;
    private $size;
    private $publicStatus;
    private $hash;
    private $createdAt;
    private $updatedAt;

    public function id(): UuidType
    {
        return $this->id;
    }

    public function basePath(): string
    {
        return $this->basePath;
    }

    public function filename(): string
    {
        return $this->filename;
    }

    public function mimeType(): string
    {
        return $this->mimeType;
    }

    public function size(): int
    {
        return $this->size;
    }

    public function publicStatus(): bool
    {
        return $this->publicStatus;
    }

    public function hash(): string
    {
        return $this->hash;
    }

    public function createdAt(): DateTimeType
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeType
    {
        return $this->updatedAt;
    }

    protected static function createDefinitions(): DefinitionCollection
    {
        return new DefinitionCollection([
            new Definition('id', UuidType::class, false, true),
            new Definition('basePath', TypeInterface::TYPE_STRING, false, true),
            new Definition('filename', TypeInterface::TYPE_STRING, false, true),
            new Definition('mimeType', TypeInterface::TYPE_STRING, false, true),
            new Definition('size', TypeInterface::TYPE_INT, false, true),
            new Definition('publicStatus', 'bool', false, true),
            new Definition('hash', TypeInterface::TYPE_STRING, false, false),
            new Definition('createdAt', DateTimeType::class, false, true),
            new Definition('updatedAt', DateTimeType::class, false, true),
        ]);
    }
}

