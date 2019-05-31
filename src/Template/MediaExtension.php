<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Template;

use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\Entity\MediaDefinitionInfo;
use Ixocreate\Media\ImageDefinition\ImageDefinitionSubManager;
use Ixocreate\Media\MediaInterface;
use Ixocreate\Media\Repository\MediaDefinitionInfoRepository;
use Ixocreate\Media\Repository\MediaRepository;
use Ixocreate\Media\Schema\Type\ImageType;
use Ixocreate\Template\Extension\ExtensionInterface;

final class MediaExtension implements ExtensionInterface
{

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

    /**
     * MediaExtension constructor.
     * @param MediaConfig $mediaConfig
     * @param MediaRepository $mediaRepository
     * @param MediaDefinitionInfoRepository $mediaDefinitionInfoRepository
     * @param ImageDefinitionSubManager $imageDefinitionSubManager
     */
    public function __construct(
        MediaConfig $mediaConfig,
        MediaRepository $mediaRepository,
        MediaDefinitionInfoRepository $mediaDefinitionInfoRepository,
        ImageDefinitionSubManager $imageDefinitionSubManager
    )
    {
        $this->mediaConfig = $mediaConfig;
        $this->mediaDefinitionInfoRepository = $mediaDefinitionInfoRepository;
        $this->mediaRepository = $mediaRepository;
        $this->imageDefinitionSubManager = $imageDefinitionSubManager;
    }

    /**
     * @return string
     */
    public static function getName(): string
    {
        return 'media';
    }

    /**
     * @return $this
     */
    public function __invoke()
    {
        return $this;
    }

    /**
     * @param ImageType $imageType
     * @return mixed
     */
    public function width(ImageType $imageType)
    {
        /** @var MediaInterface $media */
        $media = $imageType->value();

        return $media->metaData()['width'];
    }

    /**
     * @param ImageType $imageType
     * @return mixed
     */
    public function height(ImageType $imageType)
    {
        /** @var MediaInterface $media */
        $media = $imageType->value();

        return $media->metaData()['height'];
    }

    /**
     * @param ImageType $imageType
     * @param string $imageDefinitionServiceName
     * @return int
     */
    public function definitionWidth(ImageType $imageType, string $imageDefinitionServiceName)
    {
        $media = $imageType->value();

        if ($this->imageDefinitionSubManager->has($imageDefinitionServiceName)) {
            /** @var MediaDefinitionInfo $result */
            $result = $this->mediaDefinitionInfoRepository->findBy(
                ['mediaId' => $media->id(), 'imageDefinition' => $imageDefinitionServiceName]
            )[0];

            return $result->width();
        }
    }

    /**
     * @param ImageType $imageType
     * @param string $imageDefinitionServiceName
     * @return int
     */
    public function definitionHeight(ImageType $imageType, string $imageDefinitionServiceName)
    {
        $media = $imageType->value();

        if ($this->imageDefinitionSubManager->has($imageDefinitionServiceName)) {
            /** @var MediaDefinitionInfo $result */
            $result = $this->mediaDefinitionInfoRepository->findBy(
                ['mediaId' => $media->id(), 'imageDefinition' => $imageDefinitionServiceName]
            )[0];

            return $result->height();
        }
    }
}