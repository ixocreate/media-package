<?php

namespace KiwiSuite\Media\Repository;

use KiwiSuite\Media\Entity\Media;
use KiwiSuite\Media\Metadata\MediaMetadata;
use KiwiSuite\Database\Repository\AbstractRepository;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

final class MediaRepository extends AbstractRepository
{
    
    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return Media::class;
    }
    
    public function loadMetadata(ClassMetadataBuilder $builder): void
    {
        $metadata = (new MediaMetadata($builder));
    }
    
}

