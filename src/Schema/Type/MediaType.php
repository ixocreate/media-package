<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Schema\Type;

use Doctrine\DBAL\Types\GuidType;
use Ixocreate\Cache\CacheManager;
use Ixocreate\Media\Cacheable\MediaCacheable;
use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\MediaInfo;
use Ixocreate\Media\Repository\MediaRepository;
use Ixocreate\Media\Schema\Element\MediaElement;
use Ixocreate\Media\Uri\MediaUri;
use Ixocreate\Schema\Builder\BuilderInterface;
use Ixocreate\Schema\Element\ElementInterface;
use Ixocreate\Schema\Element\ElementProviderInterface;
use Ixocreate\Schema\Type\AbstractType;
use Ixocreate\Schema\Type\DatabaseTypeInterface;
use Ixocreate\Schema\Type\Type;

class MediaType extends AbstractType implements DatabaseTypeInterface, ElementProviderInterface, \Serializable
{
    /**
     * @var MediaRepository
     */
    protected $mediaRepository;

    /**
     * @var MediaUri
     */
    protected $uri;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * @var MediaCacheable
     */
    private $mediaCacheable;

    /**
     * @var MediaInfo
     */
    private $mediaInfo;

    /**
     * ImageType constructor.
     *
     * @param MediaRepository $mediaRepository
     * @param CacheManager $cacheManager
     * @param MediaCacheable $mediaCacheable
     * @param MediaUri $uri
     */
    public function __construct(
        MediaRepository $mediaRepository,
        CacheManager $cacheManager,
        MediaCacheable $mediaCacheable,
        MediaUri $uri
    )
    {
        $this->mediaRepository = $mediaRepository;
        $this->uri = $uri;
        $this->cacheManager = $cacheManager;
        $this->mediaCacheable = $mediaCacheable;
    }

    /**
     * @param $value
     * @return mixed|null|object
     */
    protected function transform($value)
    {
        if (\is_array($value)) {
            if (empty($value['id'])) {
                return null;
            }

            $value = $value['id'];
        }

        $value = $this->mediaRepository->find($value);

        if (!empty($value)) {
            return $value;
        }

        return null;
    }

    public function mediaInfo(): ?MediaInfo
    {
        return $this->mediaInfo;
    }

    public function __toString()
    {
        if (empty($this->value())) {
            return '';
        }

        return (string)$this->value()->id();
    }

    /**
     * @return mixed|null|string
     */
    public function jsonSerialize()
    {
        if (empty($this->value())) {
            return null;
        }
        $array = $this->value()->toPublicArray();
        $array['original'] = $this->getUrl();
        $array['thumb'] = $this->getUrl('admin-thumb');

        return $array;
    }

    public function convertToDatabaseValue()
    {
        if (empty($this->value())) {
            return null;
        }

        return (string)$this->value()->id();
    }

    public static function baseDatabaseType(): string
    {
        return GuidType::class;
    }

    public function getUrl(?string $definition = null): string
    {
        /** @var Media $media */
        $media = $this->value();
        if (empty($media) || !($media instanceof Media)) {
            return '';
        }

        return $this->uri->imageUrl($media, $definition);
    }

    public static function serviceName(): string
    {
        return 'media';
    }

    public function provideElement(BuilderInterface $builder): ElementInterface
    {
        return $builder->get(MediaElement::class);
    }

    public function serialize()
    {
        return \serialize($this->__toString());
    }

    /**
     * @param string $serialized
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function unserialize($serialized)
    {
        $unserialized = \unserialize($serialized);

        $mediaId = null;
        if (!empty($unserialized['value']) && $unserialized['value'] instanceof Media) {
            $mediaId = (string)$unserialized['value']->id();
        } else if (\is_string($unserialized)) {
            $mediaId = $unserialized;
        }

        if ($mediaId === null) {
            return;
        }

        /** @var MediaType $mediaType */
        $mediaType = Type::get(MediaType::serviceName());
        $this->cacheManager = $mediaType->cacheManager;
        $this->mediaCacheable = $mediaType->mediaCacheable;
        $this->mediaRepository = $mediaType->mediaRepository;
        $this->uri = $mediaType->uri;

        /** @var MediaInfo $mediaInfo */
        $mediaInfo = $this->cacheManager->fetch($this->mediaCacheable->withMediaId($mediaId));
        $this->value = $mediaInfo->media();
        $this->mediaInfo = $mediaInfo;
    }
}
