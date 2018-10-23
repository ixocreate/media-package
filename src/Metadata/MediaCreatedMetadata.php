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

final class MediaCreatedMetadata extends AbstractMetadata
{

    protected function buildMetadata(): void
    {
        $builder = $this->getBuilder();
        $builder->setTable('media_media_created');

        $this->setFieldBuilder('mediaId',
            $builder->createField('mediaId', UuidType::class)
                ->makePrimaryKey()
        )->build();

        $this->setFieldBuilder('createdBy',
            $builder->createField('createdBy', UuidType::class)
                ->makePrimaryKey()
        )->build();

    }

    public function mediaId(): FieldBuilder
    {
        return $this->getField('mediaId');
    }

    public function createdBy(): FieldBuilder
    {
        return $this->getField('createdBy');
    }
}

