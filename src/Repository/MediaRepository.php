<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Package\Media\Repository;

use Ixocreate\Package\Media\Entity\Media;
use Ixocreate\Package\Database\Repository\AbstractRepository;

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
