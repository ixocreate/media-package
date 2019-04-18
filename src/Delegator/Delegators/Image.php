<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Package\Delegator\Delegators;

use Ixocreate\Media\Package\DelegatorInterface;
use Ixocreate\Media\Package\ImageDefinitionInterface;
use Ixocreate\Media\Package\MediaInterface;
use Ixocreate\Filesystem\Package\Storage\StorageSubManager;
use Ixocreate\Media\Package\Config\MediaConfig;
use Ixocreate\Media\Package\ImageDefinition\ImageDefinitionSubManager;
use Ixocreate\Media\Package\MediaPaths;
use Ixocreate\Media\Package\Processor\ImageProcessor;

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
