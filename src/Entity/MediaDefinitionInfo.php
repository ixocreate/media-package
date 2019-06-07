<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Entity;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Ixocreate\Database\DatabaseEntityInterface;
use Ixocreate\Entity\Definition;
use Ixocreate\Entity\DefinitionCollection;
use Ixocreate\Entity\EntityInterface;
use Ixocreate\Entity\EntityTrait;
use Ixocreate\Schema\Type\DateTimeType;
use Ixocreate\Schema\Type\TypeInterface;
use Ixocreate\Schema\Type\UuidType;

final class MediaDefinitionInfo implements EntityInterface, DatabaseEntityInterface
{
    use EntityTrait;

    private $mediaId;

    private $imageDefinition;

    private $width;

    private $height;

    private $fileSize;

    private $cropParameters;

    private $createdAt;

    private $updatedAt;

    public function mediaId(): UuidType
    {
        return $this->mediaId;
    }

    public function imageDefinition(): string
    {
        return $this->imageDefinition;
    }

    public function width(): int
    {
        return $this->width;
    }

    public function height(): int
    {
        return $this->height;
    }

    public function fileSize(): int
    {
        return $this->fileSize;
    }

    public function cropParameters(): ?array
    {
        return $this->cropParameters;
    }

    public function createdAt(): DateTimeType
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeType
    {
        return $this->updatedAt;
    }

    /**
     * @return DefinitionCollection
     */
    protected static function createDefinitions(): DefinitionCollection
    {
        return new DefinitionCollection([
            new Definition('mediaId', UuidType::class, false, true),
            new Definition('imageDefinition', TypeInterface::TYPE_STRING, false, true),
            new Definition('width', TypeInterface::TYPE_INT, false, true),
            new Definition('height', TypeInterface::TYPE_INT, false, true),
            new Definition('fileSize', TypeInterface::TYPE_INT, false, true),
            new Definition('cropParameters', TypeInterface::TYPE_ARRAY, true, true),
            new Definition('createdAt', DateTimeType::class, false, true),
            new Definition('updatedAt', DateTimeType::class, false, true),
        ]);
    }

    public static function loadMetadata(ClassMetadataBuilder $builder)
    {
        $builder->setTable('media_definition_info');

        $builder->createField('mediaId', UuidType::serviceName())->makePrimaryKey()->build();
        $builder->createField('imageDefinition', 'string')->makePrimaryKey()->build();
        $builder->createField('width', Type::INTEGER)->nullable(false)->build();
        $builder->createField('height', Type::INTEGER)->nullable(false)->build();
        $builder->createField('fileSize', Type::INTEGER)->nullable(false)->build();
        $builder->createField('cropParameters', 'json')->nullable(true)->build();
        $builder->createField('createdAt', DateTimeType::serviceName())->nullable(false)->build();
        $builder->createField('updatedAt', DateTimeType::serviceName())->nullable(false)->build();
    }
}
