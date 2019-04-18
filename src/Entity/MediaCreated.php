<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Package\Entity;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Ixocreate\Entity\DatabaseEntityInterface;
use Ixocreate\Entity\Package\DefinitionCollection;
use Ixocreate\Entity\Package\EntityInterface;
use Ixocreate\Entity\Package\EntityTrait;
use Ixocreate\Entity\Package\Entity\Definition;
use Ixocreate\Type\Package\Entity\UuidType;

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
