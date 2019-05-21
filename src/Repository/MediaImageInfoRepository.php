<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Repository;

use Ixocreate\Database\Repository\AbstractRepository;
use Ixocreate\Media\Entity\MediaImageInfo;

final class MediaImageInfoRepository extends AbstractRepository
{
    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return MediaImageInfo::class;
    }
}
