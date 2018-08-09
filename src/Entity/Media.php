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

use KiwiSuite\Entity\Entity\Definition;
use KiwiSuite\Entity\Entity\DefinitionCollection;
use KiwiSuite\Entity\Entity\EntityInterface;
use KiwiSuite\Entity\Entity\EntityTrait;
use KiwiSuite\CommonTypes\Entity\UuidType;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\IntegerType;
use KiwiSuite\CommonTypes\Entity\DateTimeType;
use KiwiSuite\Entity\Type\Type;
use KiwiSuite\Entity\Type\TypeInterface;

final class Media implements EntityInterface
{
    use EntityTrait;

    private $id;
    private $basePath;
    private $filename;
    private $mimeType;
    private $size;
    private $public;
    private $hash;
    private $createdAt;
    private $updatedAt;
    private $deletedAt;

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

    public function public(): bool
    {
        return $this->public;
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

    public function deletedAt(): DateTimeType
    {
        return $this->deletedAt;
    }

    protected static function createDefinitions(): DefinitionCollection
    {
        return new DefinitionCollection([
            new Definition('id', UuidType::class, true, true),
            new Definition('basePath', TypeInterface::TYPE_STRING, true, true),
            new Definition('filename', TypeInterface::TYPE_STRING, true, true),
            new Definition('mimeType', TypeInterface::TYPE_STRING, true, true),
            new Definition('size', TypeInterface::TYPE_INT, true, true),
            new Definition('public', TypeInterface::TYPE_BOOL, true, true),
            new Definition('hash', TypeInterface::TYPE_STRING, true, true),
            new Definition('createdAt', DateTimeType::class, true, true),
            new Definition('updatedAt', DateTimeType::class, true, true),
            new Definition('deletedAt', DateTimeType::clas, true, true)
        ]);
    }
}

