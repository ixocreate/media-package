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

namespace Ixocreate\Media\Repository;

use Ixocreate\Media\Entity\MediaCreated;
use Ixocreate\Database\Repository\AbstractRepository;

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
