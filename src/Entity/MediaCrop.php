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

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use KiwiSuite\Contract\Entity\DatabaseEntityInterface;
use KiwiSuite\Entity\Entity\DefinitionCollection;
use KiwiSuite\Entity\Entity\EntityInterface;
use KiwiSuite\Entity\Entity\EntityTrait;
use KiwiSuite\Entity\Entity\Definition;
use KiwiSuite\CommonTypes\Entity\DateTimeType;
use KiwiSuite\Contract\Type\TypeInterface;
use KiwiSuite\CommonTypes\Entity\UuidType;

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
            new Definition('imageDefinition', TypeInterface::TYPE_STRING, false,true),
            new Definition('cropParameters',TypeInterface::TYPE_ARRAY,false,true),
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