<?php
declare(strict_types=1);

namespace Ixocreate\Media\Cacheable;

use DateTime;
use Ixocreate\Cache\CacheableInterface;
use Ixocreate\Media\Entity\MediaDefinitionInfo;
use Ixocreate\Media\Repository\MediaDefinitionInfoRepository;

final class UrlVariantCacheable implements CacheableInterface
{
    /**
     * @var string
     */
    private $mediaId;

    /**
     * @var string
     */
    private $imageDefinition;
    /**
     * @var MediaDefinitionInfoRepository
     */
    private $mediaDefinitionInfoRepository;

    public function __construct(MediaDefinitionInfoRepository $mediaDefinitionInfoRepository)
    {
        $this->mediaDefinitionInfoRepository = $mediaDefinitionInfoRepository;
    }

    public function withMediaId(string $mediaId): UrlVariantCacheable
    {
        $cacheable = clone $this;
        $cacheable->mediaId = $mediaId;

        return $cacheable;
    }

    /**
     * @param string $imageDefinition
     * @return UrlVariantCacheable
     */
    public function withImageDefinition(string $imageDefinition): UrlVariantCacheable
    {
        $cacheable = clone $this;
        $cacheable->imageDefinition = $imageDefinition;

        return $cacheable;
    }

    /**
     * @inheritDoc
     */
    public function uncachedResult()
    {
        /** @var MediaDefinitionInfo $mediaDefinition */
        $mediaDefinition = $this->mediaDefinitionInfoRepository->findOneBy([
            'mediaId' => $this->mediaId,
            'imageDefinition' => $this->imageDefinition,
        ]);

        if (empty($mediaDefinition)) {
            return \substr(\sha1((new DateTime())->format('c')), 0, 7);
        }

        return \substr(\sha1($mediaDefinition->updatedAt()->format('c')), 0, 7);
    }

    /**
     * @inheritDoc
     */
    public function cacheName(): string
    {
        return 'media';
    }

    /**
     * @inheritDoc
     */
    public function cacheKey(): string
    {
        return 'url.variant.' . $this->mediaId . '.' . $this->imageDefinition;
    }

    /**
     * @inheritDoc
     */
    public function cacheTtl(): int
    {
        return 3600 * 24 * 365;
    }
}
