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
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\IntegerType;
use KiwiSuite\CommonTypes\Entity\DateTimeType;

final class MediaMetadata extends AbstractMetadata
{

    protected function buildMetadata(): void
    {
        $builder = $this->getBuilder();
        $builder->setTable('media_media');

        $this->setFieldBuilder('id',
            $builder->createField('id', UuidType::class)
                ->makePrimaryKey()
        )->build();

        $this->setFieldBuilder('basePath',
            $builder->createField('basePath', Type::STRING)
        )->build();

        $this->setFieldBuilder('filename',
            $builder->createField('filename', Type::STRING)
        )->build();

        $this->setFieldBuilder('mimeType',
            $builder->createField('mimeType', Type::STRING)
        )->build();

        $this->setFieldBuilder('size',
            $builder->createField('size', Type::INTEGER)
        )->build();

        $this->setFieldBuilder('public',
            $builder->createField('public', Type::BOOLEAN)
        )->build();

        $this->setFieldBuilder('hash',
            $builder->createField('hash', Type::STRING)
        )->build();

        $this->setFieldBuilder('createdAt',
            $builder->createField('createdAt', DateTimeType::class)
        )->build();

        $this->setFieldBuilder('updatedAt',
            $builder->createField('updatedAt', DateTimeType::class)
        )->build();

        $this->setFieldBuilder('deletedAt',
            $builder->createField('deletedAt', DateTimeType::class)
        )->build();

    }

    public function id(): FieldBuilder
    {
        return $this->getField('id');
    }

    public function basePath(): FieldBuilder
    {
        return $this->getField('basePath');
    }

    public function filename(): FieldBuilder
    {
        return $this->getField('filename');
    }

    public function mimeType(): FieldBuilder
    {
        return $this->getField('mimeType');
    }

    public function size(): FieldBuilder
    {
        return $this->getField('size');
    }

    public function public(): FieldBuilder
    {
        return $this->getField('public');
    }

    public function hash(): FieldBuilder
    {
        return $this->getField('hash');
    }

    public function createdAt(): FieldBuilder
    {
        return $this->getField('createdAt');
    }

    public function updatedAt(): FieldBuilder
    {
        return $this->getField('updatedAt');
    }

    public function deletedAt(): FieldBuilder
    {
        return $this->getField('deletedAt');
    }
}

