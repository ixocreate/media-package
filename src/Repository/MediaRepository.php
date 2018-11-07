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

use KiwiSuite\Media\Entity\Media;
use KiwiSuite\Database\Repository\AbstractRepository;

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

