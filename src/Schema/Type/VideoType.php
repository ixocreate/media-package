<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Schema\Type;

use Doctrine\DBAL\Types\GuidType;
use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\Schema\Element\VideoElement;
use Ixocreate\Media\Uri\MediaUri;
use Ixocreate\Schema\Builder\BuilderInterface;
use Ixocreate\Schema\Element\ElementInterface;
use Ixocreate\Schema\Element\ElementProviderInterface;
use Ixocreate\Schema\Type\AbstractType;
use Ixocreate\Schema\Type\DatabaseTypeInterface;
use Ixocreate\Schema\Type\Type;
use Ixocreate\Schema\Type\TypeInterface;

final class VideoType extends AbstractType implements DatabaseTypeInterface, ElementProviderInterface, \Serializable
{
    /**
     * @var MediaType
     */
    private $mediaType;

    /**
     * @var MediaUri
     */
    private $uri;

    /**
     * @var MediaConfig
     */
    private $mediaConfig;

    public function __construct(MediaUri $uri, MediaConfig $mediaConfig)
    {
        $this->uri = $uri;
        $this->mediaConfig = $mediaConfig;
    }

    /**
     * @param $value
     * @param array $options
     * @return TypeInterface
     */
    public function create($value, array $options = []): TypeInterface
    {
        $type = clone $this;
        $mediaType = Type::create($value, MediaType::class);

        if (!empty($mediaType->value()) && \in_array(
            $mediaType->value()->mimeType(),
            $this->mediaConfig->videoWhitelist()
        )) {
            $type->mediaType = $mediaType;
        }

        return $type;
    }

    /**
     * @return mixed
     */
    public function value()
    {
        if (empty($this->mediaType)) {
            return null;
        }

        if (empty($this->mediaType->value())) {
            return null;
        }

        return $this->mediaType->value();
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

        $array = $this->mediaType->jsonSerialize();
        $array['thumb'] = $this->getUrl('admin-thumb');

        return $array;
    }

    public function getUrl(?string $imageDefinition = null): string
    {
        /** @var Media $media */
        $media = $this->value();
        if (empty($media) || !($media instanceof Media)) {
            return "";
        }

        return $this->uri->imageUrl($media, $imageDefinition);
    }

    public function convertToDatabaseValue()
    {
        if (empty($this->value())) {
            return null;
        }

        return $this->mediaType->convertToDatabaseValue();
    }

    public static function baseDatabaseType(): string
    {
        return GuidType::class;
    }

    public static function serviceName(): string
    {
        return 'video';
    }

    public function provideElement(BuilderInterface $builder): ElementInterface
    {
        return $builder->get(VideoElement::class);
    }

    /**
     * @return string|void
     */
    public function serialize()
    {
        return \serialize([
            'mediaType' => $this->mediaType,
        ]);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        /** @var VideoType $type */
        $type = Type::get(VideoType::serviceName());
        $this->uri = $type->uri;
        $this->mediaConfig = $type->mediaConfig;

        $unserialized = \unserialize($serialized);
        if (!empty($unserialized['mediaType']) && $unserialized['mediaType'] instanceof MediaType) {
            $this->mediaType = $unserialized['mediaType'];
        }
    }
}
