<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Cacheable;

use Ixocreate\Cache\CacheableInterface;
use Ixocreate\Media\Repository\MediaRepository;

final class MediaCacheable implements CacheableInterface
{
    private $mediaId;

    /**
     * @var MediaRepository
     */
    private $mediaRepository;

    /**
     * MediaCacheable constructor.
     * @param MediaRepository $mediaRepository
     */
    public function __construct(
        MediaRepository $mediaRepository
    )
    {
        $this->mediaRepository = $mediaRepository;
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
        return $this->mediaRepository->find($this->mediaId);
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
        return 'media.' . (string) $this->mediaId;
    }

    /**
     * @return int
     */
    public function cacheTtl(): int
    {
        return PHP_INT_MAX;
    }
}
