<?php
declare(strict_types=1);

namespace Ixocreate\Media\Cacheable;

use Ixocreate\Cache\CacheableInterface;
use Ixocreate\Media\Entity\MediaDefinitionInfo;
use Ixocreate\Media\Repository\MediaDefinitionInfoRepository;
use Ixocreate\Media\Repository\MediaRepository;

final class MediaDefinitionInfoCacheable implements CacheableInterface
{

    private $mediaId;

    /**
     * @var string
     */
    private $imageDefinition;

    /**
     * @var MediaRepository
     */
    private $mediaRepository;

    /**
     * @var MediaDefinitionInfoRepository
     */
    private $mediaDefinitionInfoRepository;

    /**
     * MediaDefinitionInfoCacheable constructor.
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
     * @param string $mediaId
     * @return MediaDefinitionInfoCacheable
     */
    public function withMediaId(string $mediaId): MediaDefinitionInfoCacheable
    {
        $cacheable = clone $this;
        $cacheable->mediaId = $mediaId;

        return $cacheable;
    }

    /**
     * @param string $imageDefinition
     * @return MediaDefinitionInfoCacheable
     */
    public function withImageDefinition(string $imageDefinition): MediaDefinitionInfoCacheable
    {
        $cacheable = clone $this;
        $cacheable->imageDefinition = $imageDefinition;

        return $cacheable;
    }

    /**
     * @return mixed
     */
    public function uncachedResult()
    {
        /** @var MediaDefinitionInfo $mediaDefinition */
        $mediaDefinition = $this->mediaDefinitionInfoRepository->findOneBy([
            'mediaId' => $this->mediaId,
            'imageDefinition' => $this->imageDefinition,
        ]);

        return $mediaDefinition;
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
        return 'media.definitionInfo' . (string) $this->mediaId . '.' . $this->imageDefinition;
    }

    /**
     * @return int
     */
    public function cacheTtl(): int
    {
        return PHP_INT_MAX;
    }
}
