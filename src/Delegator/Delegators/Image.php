<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Package\Media\Delegator\Delegators;

use Ixocreate\Package\Media\DelegatorInterface;
use Ixocreate\Package\Media\ImageDefinitionInterface;
use Ixocreate\Package\Media\MediaInterface;
use Ixocreate\Package\Filesystem\Storage\StorageSubManager;
use Ixocreate\Package\Media\Config\MediaConfig;
use Ixocreate\Package\Media\ImageDefinition\ImageDefinitionSubManager;
use Ixocreate\Package\Media\MediaPaths;
use Ixocreate\Package\Media\Processor\ImageProcessor;

final class Image implements DelegatorInterface
{
    /**
     * @var array
     */
    private $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
    ];

    /**
     * @var array
     */
    private $allowedFileExtensions = [
        'jpeg',
        'jpg',
        'jpe',
        'png',
        'gif',
    ];

    /**
     * @var ImageDefinitionSubManager
     */
    private $imageDefinitionSubManager;

    /**
     * @var MediaConfig
     */
    private $mediaConfig;

    /**
     * @var StorageSubManager
     */
    private $storageSubManager;

    /**
     * Image constructor.
     *
     * @param ImageDefinitionSubManager $imageDefinitionSubManager
     * @param MediaConfig $mediaConfig
     * @param StorageSubManager $storageSubManager
     */
    public function __construct(
        ImageDefinitionSubManager $imageDefinitionSubManager,
        MediaConfig $mediaConfig,
        StorageSubManager $storageSubManager
    ) {
        $this->imageDefinitionSubManager = $imageDefinitionSubManager;
        $this->mediaConfig = $mediaConfig;
        $this->storageSubManager = $storageSubManager;
    }

    /**
     * @return string
     */
    public static function serviceName(): string
    {
        return "Image";
    }

    /**
     * @param MediaInterface $media
     * @return bool
     */
    public function isResponsible(MediaInterface $media): bool
    {
        $pathInfo = \pathinfo($media->filename());
        $extension = $pathInfo['extension'];
        if ((!\in_array($media->mimeType(), $this->allowedMimeTypes)) &&
            (!\in_array($extension, $this->allowedFileExtensions))) {
            return false;
        }
        return true;
    }

    /**
     * @param MediaInterface $media
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function process(MediaInterface $media): void
    {
        $storage = $this->storageSubManager->get('media');

        foreach ($this->imageDefinitionSubManager->getServices() as $imageDefinitionClassName) {
            /** @var ImageDefinitionInterface $imageDefinition */
            $imageDefinition = $this->imageDefinitionSubManager->get($imageDefinitionClassName);

            (new ImageProcessor($media, $imageDefinition, $this->mediaConfig, $storage))->process();
        }
    }

    /**
     * @return array
     */
    public function directories(): array
    {
        $directories = [];
        foreach ($this->imageDefinitionSubManager->getServices() as $imageDefinitionClassName) {
            /** @var ImageDefinitionInterface $imageDefinition */
            $imageDefinition = $this->imageDefinitionSubManager->get($imageDefinitionClassName);

            $directories[] = MediaPaths::IMAGE_DEFINITION_PATH . $imageDefinition->directory() . '/';
        }
        return $directories;
    }
}
