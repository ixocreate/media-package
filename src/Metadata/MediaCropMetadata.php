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

namespace KiwiSuite\Media\Metadata;

use Doctrine\DBAL\Types\Type;
use KiwiSuite\Database\ORM\Metadata\AbstractMetadata;
use Doctrine\ORM\Mapping\Builder\FieldBuilder;
use KiwiSuite\CommonTypes\Entity\UuidType;
use KiwiSuite\CommonTypes\Entity\DateTimeType;

final class MediaCropMetadata extends AbstractMetadata
{

    protected function buildMetadata(): void
    {
        $builder = $this->getBuilder();
        $builder->setTable('media_media_crop');

        $this->setFieldBuilder('id',
            $builder->createField('id', UuidType::class)
                ->makePrimaryKey()
        )->build();

        $this->setFieldBuilder('mediaId',
            $builder->createField('mediaId',UuidType::class)
        )->build();

        $this->setFieldBuilder('imageDefinition',
            $builder->createField('imageDefinition', Type::STRING)
        )->build();

        $this->setFieldBuilder('cropParameters',
            $builder->createField('cropParameters', Type::JSON)
        )->build();

        $this->setFieldBuilder('createdAt',
            $builder->createField('createdAt', DateTimeType::class)
        )->build();

        $this->setFieldBuilder('updatedAt',
            $builder->createField('updatedAt', DateTimeType::class)
        )->build();

    }

    public function id(): FieldBuilder
    {
        return $this->getField('id');
    }

    public function mediaId(): FieldBuilder
    {
        return $this->getField('mediaId');
    }

    public function imageDefinition(): FieldBuilder
    {
        return $this->getField('imageDefinition');
    }

    public function cropParameters(): FieldBuilder
    {
        return $this->getField('cropParameters');
    }

    public function createdAt(): FieldBuilder
    {
        return $this->getField('createdAt');
    }

    public function updatedAt(): FieldBuilder
    {
        return $this->getField('updatedAt');
    }
}

