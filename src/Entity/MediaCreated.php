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
use Ixocreate\Entity\Entity\DefinitionCollection;
use Ixocreate\Entity\Entity\EntityInterface;
use Ixocreate\Entity\Entity\EntityTrait;
use Ixocreate\Entity\Entity\Definition;
use Ixocreate\CommonTypes\Entity\UuidType;

final class MediaCreated implements EntityInterface, DatabaseEntityInterface
{
    use EntityTrait;

    private $mediaId;
    private $createdBy;

    public function mediaId(): UuidType
    {
        return $this->mediaId;
    }

    public function createdBy(): UuidType
    {
        return $this->createdBy;
    }

    /**
     * @return DefinitionCollection
     */
    protected static function createDefinitions(): DefinitionCollection
    {
        return new DefinitionCollection([
            new Definition('mediaId', UuidType::class, false, true),
            new Definition('createdBy', UuidType::class, false, true),
        ]);
    }

    public static function loadMetadata(ClassMetadataBuilder $builder)
    {
        $builder->setTable('media_media_created');

        $builder->createField('mediaId', UuidType::serviceName())->makePrimaryKey()->build();
        $builder->createField('createdBy', UuidType::serviceName())->makePrimaryKey()->build();
    }
}
