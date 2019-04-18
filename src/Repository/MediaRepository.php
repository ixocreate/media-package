<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Package\Repository;

use Ixocreate\Media\Package\Entity\Media;
use Ixocreate\Database\Package\Repository\AbstractRepository;

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
