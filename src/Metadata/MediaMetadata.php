<?php

/**
 * kiwi-suite/admin (https://github.com/kiwi-suite/admin)
 *
 * @package kiwi-suite/admin
 * @see https://github.com/kiwi-suite/admin
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

        $this->setFieldBuilder('createdAt',
            $builder->createField('createdAt', DateTimeType::class)
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

    public function createdAt(): FieldBuilder
    {
        return $this->getField('createdAt');
    }

}

