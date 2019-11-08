<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Cacheable;

use Ixocreate\Cache\CacheableInterface;
use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\Entity\MediaDefinitionInfo;
use Ixocreate\Media\Exception\InvalidArgumentException;
use Ixocreate\Media\MediaInfo;
use Ixocreate\Media\Repository\MediaDefinitionInfoRepository;
use Ixocreate\Media\Repository\MediaRepository;

final class MediaCacheable implements CacheableInterface
{
    private $mediaId;

    /**
     * @var MediaRepository
     */
    private $mediaRepository;

    /**
     * @var MediaDefinitionInfoRepository
     */
    private $mediaDefinitionInfoRepository;

    /**
     * MediaCacheable constructor.
     * @param MediaRepository $mediaRepository
     * @param MediaDefinitionInfoRepository $mediaDefinitionInfoRepository
     */
    public function __construct(
        MediaRepository $mediaRepository,
        MediaDefinitionInfoRepository $mediaDefinitionInfoRepository
    )
    {
        $this->mediaRepository = $mediaRepository;
        $this->mediaDefinitionInfoRepository = $mediaDefinitionInfoRepository;
    }

    /**
     * @param $mediaId
     * @return MediaCacheable
     */
    public function withMediaId($mediaId): MediaCacheable
    {
        $cacheable = clone $this;
        $cacheable->mediaId = $mediaId;

        return $cacheable;
    }

    /**
     * @return mixed
     */
    public function uncachedResult()
    {
        if (!$this->mediaId) {
            throw new InvalidArgumentException('mediaId is empty');
        }

        /** @var Media $media */
        $media = $this->mediaRepository->find($this->mediaId);

        $mediaDefinitions = $this->mediaDefinitionInfoRepository->findBy([
            'mediaId' => $this->mediaId
        ]);
        $definitionInfos = [];
        foreach ($mediaDefinitions as $mediaDefinition) {
            $definitionInfos[$mediaDefinition->imageDefinition] = $mediaDefinition;
        }

        return new MediaInfo($media, $definitionInfos);
    }

    /**
     * @return string
     */
    public function cacheName(): string
    {
        return 'media';
    }

    /**
     * @return string
     */
    public function cacheKey(): string
    {
        return 'media.' . (string)$this->mediaId;
    }

    /**
     * @return int
     */
    public function cacheTtl(): int
    {
        return PHP_INT_MAX;
    }
}
