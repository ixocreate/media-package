<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Schema\Type;

use Doctrine\DBAL\Types\GuidType;
use Ixocreate\Collection\Collection;
use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\ImageDefinition\ImageDefinitionSubManager;
use Ixocreate\Media\Repository\MediaDefinitionInfoRepository;
use Ixocreate\Media\Repository\MediaRepository;
use Ixocreate\Media\Schema\Element\ImageElement;
use Ixocreate\Media\Uri\MediaUri;
use Ixocreate\Schema\Builder\BuilderInterface;
use Ixocreate\Schema\Element\ElementInterface;
use Ixocreate\Schema\Element\ElementProviderInterface;
use Ixocreate\Schema\Type\AbstractType;
use Ixocreate\Schema\Type\DatabaseTypeInterface;
use Ixocreate\Schema\Type\Type;
use Ixocreate\Schema\Type\TypeInterface;

final class ImageType extends AbstractType implements DatabaseTypeInterface, ElementProviderInterface, \Serializable
{
    /**
     * @var MediaType
     */
    private $mediaType;

    /**
     * @var array
     */
    private $imageDefinitionInfos;

    /**
     * @var MediaUri
     */
    private $uri;

    /**
     * @var MediaConfig
     */
    private $mediaConfig;

    /**
     * @var MediaRepository
     */
    private $mediaRepository;

    /**
     * @var MediaDefinitionInfoRepository
     */
    private $mediaDefinitionInfoRepository;

    /**
     * @var ImageDefinitionSubManager
     */
    private $imageDefinitionSubManager;

    public function __construct(
        MediaUri $uri,
        MediaConfig $mediaConfig,
        MediaRepository $mediaRepository,
        MediaDefinitionInfoRepository $mediaDefinitionInfoRepository,
        ImageDefinitionSubManager $imageDefinitionSubManager
    ) {
        $this->uri = $uri;
        $this->mediaConfig = $mediaConfig;
        $this->mediaRepository = $mediaRepository;
        $this->mediaDefinitionInfoRepository = $mediaDefinitionInfoRepository;
        $this->imageDefinitionSubManager = $imageDefinitionSubManager;
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

        if (!empty($mediaType->value()) && \in_array($mediaType->value()->mimeType(), $this->mediaConfig->imageWhitelist())) {
            $type->mediaType = $mediaType;

            $type->imageDefinitionInfos = (new Collection($this->mediaDefinitionInfoRepository
                ->findBy(['mediaId' => $mediaType->value()->id()]), 'imageDefinition'))->toArray();
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

    /**
     * @param string|null $imageDefinitionServiceName
     * @return int|null
     */
    public function width(string $imageDefinitionServiceName = null)
    {
        if (!empty($imageDefinitionServiceName)) {
            $mediaDefinitionInfo = $this->imageDefinitionInfos[$imageDefinitionServiceName] ?? null;
            return $mediaDefinitionInfo ? $mediaDefinitionInfo->width() : null;
        }
        return $this->value()->metaData()['width'];
    }

    /**
     * @param string|null $imageDefinitionServiceName
     * @return int|null
     */
    public function height(string $imageDefinitionServiceName = null)
    {
        if (!empty($imageDefinitionServiceName)) {
            $mediaDefinitionInfo = $this->imageDefinitionInfos[$imageDefinitionServiceName] ?? null;
            return $mediaDefinitionInfo ? $mediaDefinitionInfo->height() : null;
        }
        return $this->value()->metaData()['height'];
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
        return 'image';
    }

    public function provideElement(BuilderInterface $builder): ElementInterface
    {
        return $builder->get(ImageElement::class);
    }

    /**
     * @return string|void
     */
    public function serialize()
    {
        return \serialize([
            'mediaType' => $this->mediaType,
            'imageDefinitionInfos' => $this->imageDefinitionInfos,
        ]);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        /** @var ImageType $type */
        $type = Type::get(ImageType::serviceName());
        $this->uri = $type->uri;
        $this->mediaConfig = $type->mediaConfig;
        $this->imageDefinitionInfos = $type->imageDefinitionInfos;

        $unserialized = \unserialize($serialized);
        if (!empty($unserialized['mediaType']) && $unserialized['mediaType'] instanceof MediaType) {
            $this->mediaType = $unserialized['mediaType'];
        }
        if (!empty($unserialized['imageDefinitionInfos'])) {
            $this->imageDefinitionInfos = $unserialized['imageDefinitionInfos'];
        }
    }
}
