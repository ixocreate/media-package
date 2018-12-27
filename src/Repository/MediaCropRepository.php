<?php
/**
 * @see https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Repository;

use Ixocreate\Media\Entity\MediaCrop;
use Ixocreate\Database\Repository\AbstractRepository;

final class MediaCropRepository extends AbstractRepository
{
    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return MediaCrop::class;
    }
}
