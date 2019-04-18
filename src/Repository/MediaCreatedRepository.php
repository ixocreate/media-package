<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Package\Repository;

use Ixocreate\Media\Package\Entity\MediaCreated;
use Ixocreate\Database\Package\Repository\AbstractRepository;

final class MediaCreatedRepository extends AbstractRepository
{
    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return MediaCreated::class;
    }
}
