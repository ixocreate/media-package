<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Repository;

use Ixocreate\Database\Repository\AbstractRepository;
use Ixocreate\Media\Entity\MediaDefinitionInfo;

final class MediaDefinitionInfoRepository extends AbstractRepository
{
    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return MediaDefinitionInfo::class;
    }
}
