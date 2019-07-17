<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Link;

use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\Repository\MediaRepository;
use Ixocreate\Media\Uri\MediaUri;
use Ixocreate\Schema\Link\LinkInterface;

final class MediaLink implements LinkInterface
{
    /**
     * @var MediaRepository
     */
    private $mediaRepository;

    /**
     * @var MediaUri
     */
    private $mediaUri;

    /**
     * @var Media
     */
    private $media = null;

    /**
     * MediaLink constructor.
     * @param MediaRepository $mediaRepository
     * @param MediaUri $mediaUri
     */
    public function __construct(MediaRepository $mediaRepository, MediaUri $mediaUri)
    {
        $this->mediaRepository = $mediaRepository;
        $this->mediaUri = $mediaUri;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return \serialize($this->media);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $this->media = \unserialize($serialized);
    }

    /**
     * @param $value
     * @return LinkInterface
     */
    public function create($value): LinkInterface
    {
        $clone = clone $this;
        if ($value instanceof MediaLink) {
            $clone->media = $value->media;
        } elseif (\is_string($value)) {
            $value = $this->mediaRepository->find($value);
            if ($value instanceof Media) {
                $clone->media = $value;
            }
        } elseif (\is_array($value)) {
            if (!empty($value['id'])) {
                $value = $this->mediaRepository->find($value['id']);
                if ($value instanceof Media) {
                    $clone->media = $value;
                }
            }
        }

        return $clone;
    }

    /**
     * @return string
     */
    public function label(): string
    {
        return 'Media';
    }

    /**
     * @return string
     */
    public function assemble(): string
    {
        if (empty($this->media)) {
            return '';
        }

        return $this->mediaUri->url($this->media);
    }

    /**
     * @return mixed
     */
    public function toJson()
    {
        if (empty($this->media)) {
            return null;
        }

        return $this->media->toPublicArray();
    }

    /**
     * @return mixed
     */
    public function toDatabase()
    {
        if (empty($this->media)) {
            return null;
        }

        return (string) $this->media->id();
    }

    public static function serviceName(): string
    {
        return 'media';
    }
}
