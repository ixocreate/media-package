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

namespace KiwiSuite\Media\Repository;

use KiwiSuite\Media\Entity\MediaCreated;
use KiwiSuite\Media\Metadata\MediaCreatedMetadata;
use KiwiSuite\Database\Repository\AbstractRepository;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

final class MediaCreatedRepository extends AbstractRepository
{

    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return MediaCreated::class;
    }

    /**
     * @param ClassMetadataBuilder $builder
     */
    public function loadMetadata(ClassMetadataBuilder $builder): void
    {
        $metadata = (new MediaCreatedMetadata($builder));
    }

}

