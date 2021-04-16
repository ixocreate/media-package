<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media;

use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\Entity\MediaDefinitionInfo;

final class MediaInfo
{
    /**
     * @var Media
     */
    private $media;

    /**
     * @var MediaDefinitionInfo[]
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

    public function urlVariant(string $imageDefinition)
    {
        if (\array_key_exists($imageDefinition, $this->definitionInfos)) {
            return \mb_substr(\sha1($this->definitionInfos[$imageDefinition]->updatedAt()->format('c')), 0, 8);
        }

        return '';
    }
}
