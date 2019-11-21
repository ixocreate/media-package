<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media;

use Ixocreate\Media\Entity\Media;

final class MediaInfo
{
    /**
     * @var Media
     */
    private $media;

    /**
     * @var array
     */
    private $definitionInfos;

    /**
     * MediaInfo constructor.
     * @param Media $media
     * @param array $definitionInfos
     */
    public function __construct(Media $media, array $definitionInfos)
    {
        $this->media = $media;
        $this->definitionInfos = $definitionInfos;
    }

    public function media(): Media
    {
        return $this->media;
    }

    public function definitionInfos(): array
    {
        return $this->definitionInfos;
    }
}
