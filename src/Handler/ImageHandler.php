<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Handler;

use Ixocreate\Filesystem\FilesystemManager;
use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\ImageDefinition\ImageDefinitionSubManager;
use Ixocreate\Media\ImageDefinitionInterface;
use Ixocreate\Media\MediaHandlerInterface;
use Ixocreate\Media\MediaInterface;
use Ixocreate\Media\MediaPaths;
use Ixocreate\Media\Processor\ImageProcessor;

final class ImageHandler implements MediaHandlerInterface
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
     * @var FilesystemManager
     */
    private $filesystemManager;

    /**
     * Image constructor.
     *
     * @param ImageDefinitionSubManager $imageDefinitionSubManager
     * @param MediaConfig $mediaConfig
     * @param FilesystemManager $filesystemManager
     */
    public function __construct(
        ImageDefinitionSubManager $imageDefinitionSubManager,
        MediaConfig $mediaConfig,
        FilesystemManager $filesystemManager
    ) {
        $this->imageDefinitionSubManager = $imageDefinitionSubManager;
        $this->mediaConfig = $mediaConfig;
        $this->filesystemManager = $filesystemManager;
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
        $filesystem = $this->filesystemManager->get('media');

        foreach ($this->imageDefinitionSubManager->getServices() as $imageDefinitionClassName) {
            /** @var ImageDefinitionInterface $imageDefinition */
            $imageDefinition = $this->imageDefinitionSubManager->get($imageDefinitionClassName);

            (new ImageProcessor($media, $imageDefinition, $this->mediaConfig, $filesystem))->process();
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
