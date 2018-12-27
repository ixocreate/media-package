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

namespace Ixocreate\Media\Entity;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Ixocreate\Contract\Entity\DatabaseEntityInterface;
use Ixocreate\Contract\Type\TypeInterface;
use Ixocreate\Entity\Entity\Definition;
use Ixocreate\Entity\Entity\DefinitionCollection;
use Ixocreate\Entity\Entity\EntityInterface;
use Ixocreate\Entity\Entity\EntityTrait;
use Ixocreate\CommonTypes\Entity\UuidType;
use Ixocreate\CommonTypes\Entity\DateTimeType;

final class Media implements EntityInterface, DatabaseEntityInterface
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

    public static function loadMetadata(ClassMetadataBuilder $builder)
    {
        $builder->setTable('media_media');

        $builder->createField('id', UuidType::serviceName())->makePrimaryKey()->build();
        $builder->createField('basePath', 'string')->nullable(false)->build();
        $builder->createField('filename', 'string')->nullable(false)->build();
        $builder->createField('mimeType', 'string')->nullable(false)->build();
        $builder->createField('size', 'integer')->nullable(false)->build();
        $builder->createField('publicStatus', 'boolean')->nullable(false)->build();
        $builder->createField('hash', 'string')->nullable(false)->build();
        $builder->createField('createdAt', DateTimeType::serviceName())->nullable(false)->build();
        $builder->createField('updatedAt', DateTimeType::serviceName())->nullable(false)->build();
    }
}
