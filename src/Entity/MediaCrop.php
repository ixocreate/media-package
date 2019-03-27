<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Entity;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Ixocreate\Contract\Entity\DatabaseEntityInterface;
use Ixocreate\Entity\Entity\DefinitionCollection;
use Ixocreate\Entity\Entity\EntityInterface;
use Ixocreate\Entity\Entity\EntityTrait;
use Ixocreate\Entity\Entity\Definition;
use Ixocreate\CommonTypes\Entity\DateTimeType;
use Ixocreate\Contract\Type\TypeInterface;
use Ixocreate\CommonTypes\Entity\UuidType;

final class MediaCrop implements EntityInterface, DatabaseEntityInterface
{
    use EntityTrait;

    private $id;

    private $mediaId;

    private $imageDefinition;

    private $cropParameters;

    private $createdAt;

    private $updatedAt;

    public function id(): UuidType
    {
        return $this->id;
    }

    public function mediaId(): UuidType
    {
        return $this->mediaId;
    }

    public function imageDefinition(): string
    {
        return $this->imageDefinition;
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
            new Definition('id', UuidType::class, false, true),
            new Definition('mediaId', UuidType::class, false, true),
            new Definition('imageDefinition', TypeInterface::TYPE_STRING, false, true),
            new Definition('cropParameters', TypeInterface::TYPE_ARRAY, false, true),
            new Definition('createdAt', DateTimeType::class, false, true),
            new Definition('updatedAt', DateTimeType::class, false, true),
        ]);
    }

    public static function loadMetadata(ClassMetadataBuilder $builder)
    {
        $builder->setTable('media_media_crop');

        $builder->createField('id', UuidType::serviceName())->makePrimaryKey()->build();
        $builder->createField('mediaId', UuidType::serviceName())->nullable(false)->build();
        $builder->createField('imageDefinition', 'string')->nullable(false)->build();
        $builder->createField('cropParameters', 'json')->nullable(false)->build();
        $builder->createField('createdAt', DateTimeType::serviceName())->nullable(false)->build();
        $builder->createField('updatedAt', DateTimeType::serviceName())->nullable(false)->build();
    }
}
