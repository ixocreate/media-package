<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Schema\Type;

use Doctrine\DBAL\Types\JsonType;
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

final class ImageAnnotatedType extends AbstractType implements DatabaseTypeInterface, ElementProviderInterface, \Serializable
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
     * @var array
     */
    private $annotations;

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

        $type->annotations = $value['annotations'] ?? null;

        $mediaType = Type::create($value['media'] ?? null, MediaType::class);

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
            return ['media' => null, 'annotations' => $this->annotations];
        }

        if (empty($this->mediaType->value())) {
            return ['media' => null, 'annotations' => $this->annotations];
        }

        return [
            'media' => $this->mediaType->value(),
            'annotations' => $this->annotations,
        ];
    }

    public function __toString()
    {
        if (empty($this->value())) {
            return '';
        }

        return (string)$this->value()['media']->id();
    }

    /**
     * @return mixed|null|string
     */
    public function jsonSerialize()
    {
        if (empty($this->value())) {
            return null;
        }

        $array['media'] = $this->mediaType->jsonSerialize();
        $array['media']['thumb'] = $this->getUrl('admin-thumb');
        $array['annotations'] = $this->annotations;

        return $array;
    }

    public function getUrl(?string $imageDefinition = null): string
    {
        /** @var Media $media */
        $media = $this->value()['media'];
        if (empty($media) || !($media instanceof Media)) {
            return '';
        }

        return $this->uri->imageUrl($media, $imageDefinition);
    }

    /**
     * @return Collection
     */
    public function annotations()
    {
        return new Collection($this->value()['annotations'] ?? []);
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
        return $this->value()['media']->metaData()['width'];
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
        return $this->value()['media']->metaData()['height'];
    }

    public function convertToDatabaseValue()
    {
        return [
            'media' => !empty($this->value()['media']) ? $this->mediaType->convertToDatabaseValue() : null,
            'annotations' => $this->value()['annotations'] ?? null,
        ];
    }

    public static function baseDatabaseType(): string
    {
        return JsonType::class;
    }

    public static function serviceName(): string
    {
        return 'imageAnnotated';
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
            'annotations' => $this->annotations,
            'imageDefinitionInfos' => $this->imageDefinitionInfos,
        ]);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        /** @var ImageAnnotatedType $type */
        $type = Type::get(ImageAnnotatedType::serviceName());
        $this->uri = $type->uri;
        $this->mediaConfig = $type->mediaConfig;
        $this->imageDefinitionInfos = $type->imageDefinitionInfos;
        $this->annotations = $type->annotations;

        $unserialized = \unserialize($serialized);
        if (!empty($unserialized['mediaType']) && $unserialized['mediaType'] instanceof MediaType) {
            $this->mediaType = $unserialized['mediaType'];
        }
        if (!empty($unserialized['imageDefinitionInfos'])) {
            $this->imageDefinitionInfos = $unserialized['imageDefinitionInfos'];
        }
        if (!empty($unserialized['annotations'])) {
            $this->annotations = $unserialized['annotations'];
        }
    }
}
