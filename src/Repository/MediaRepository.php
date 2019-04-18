<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Repository;

use Ixocreate\Media\Entity\Media;
use Ixocreate\Database\Repository\AbstractRepository;

final class MediaRepository extends AbstractRepository
{
    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return Media::class;
    }
}
