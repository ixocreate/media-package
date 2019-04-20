<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Type;

use Doctrine\DBAL\Types\GuidType;
use Ixocreate\Entity\Type\AbstractType;
use Ixocreate\Entity\Type\Type;
use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\Repository\MediaRepository;
use Ixocreate\Media\Uri\MediaUri;
use Ixocreate\Schema\BuilderInterface;
use Ixocreate\Schema\ElementInterface;
use Ixocreate\Schema\ElementProviderInterface;
use Ixocreate\Schema\Elements\MediaElement;
use Ixocreate\Type\DatabaseTypeInterface;

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
     * ImageType constructor.
     *
     * @param MediaRepository $mediaRepository
     * @param MediaUri $uri
     */
    public function __construct(MediaRepository $mediaRepository, MediaUri $uri)
    {
        $this->mediaRepository = $mediaRepository;
        $this->uri = $uri;
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

    public function __toString()
    {
        if (empty($this->value())) {
            return "";
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
            return "";
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

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        /** @var MediaType $mediaType */
        $mediaType = Type::get(MediaType::serviceName());

        $this->mediaRepository = $mediaType->mediaRepository;
        $this->uri = $mediaType->uri;

        $this->value = null;
        $unserialized = \unserialize($serialized);
        if (!empty($unserialized['value']) && $unserialized['value'] instanceof Media) {
            $this->value = $unserialized['value'];
        }
    }
}
