<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Package\Repository;

use Ixocreate\Media\Package\Entity\MediaCrop;
use Ixocreate\Database\Package\Repository\AbstractRepository;

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
