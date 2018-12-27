<?php
/**
 * kiwi-suite/media (https://github.com/kiwi-suite/media)
 *
 * @package kiwi-suite/media
 * @see https://github.com/kiwi-suite/media
 * @copyright Copyright (c) 2010 - 2018 kiwi suite GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Delegator\Delegators;

use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\Delegator\DelegatorInterface;
use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\ImageDefinition\ImageDefinitionInterface;
use Ixocreate\Media\ImageDefinition\ImageDefinitionSubManager;
use Ixocreate\Media\Processor\ImageProcessor;

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
     * Image constructor.
     *
     * @param ImageDefinitionSubManager $imageDefinitionSubManager
     * @param MediaConfig $mediaConfig
     */
    public function __construct(ImageDefinitionSubManager $imageDefinitionSubManager, MediaConfig $mediaConfig)
    {
        $this->imageDefinitionSubManager = $imageDefinitionSubManager;
        $this->mediaConfig = $mediaConfig;
    }

    /**
     * @return string
     */
    public static function serviceName(): string
    {
        return "Image";
    }

    /**
     * @param Media $media
     * @return bool
     */
    public function isResponsible(Media $media): bool
    {
        $pathInfo = \pathinfo($media->filename());
        $extension = $pathInfo['extension'];
        $responsible = true;
        if ((!\in_array($media->mimeType(), $this->allowedMimeTypes)) &&
            (!\in_array($extension, $this->allowedFileExtensions))) {
            $responsible = false;
        }
        return $responsible;
    }

    /**
     * @param Media $media
     */
    public function process(Media $media): void
    {
        foreach ($this->imageDefinitionSubManager->getServiceManagerConfig()->getNamedServices() as $name => $imageDefinitionClassName) {
            /** @var ImageDefinitionInterface $imageDefinition */
            $imageDefinition = $this->imageDefinitionSubManager->get($imageDefinitionClassName);

            $imageProcessor = new ImageProcessor($media, $imageDefinition, $this->mediaConfig);
            $imageProcessor->process();
        }
    }

    /**
     * @return array
     */
    public function directories(): array
    {
        $directories = [];
        foreach ($this->imageDefinitionSubManager->getServiceManagerConfig()->getNamedServices() as $imageDefinitionClassName) {
            /** @var ImageDefinitionInterface $imageDefinition */
            $imageDefinition = $this->imageDefinitionSubManager->get($imageDefinitionClassName);

            $directories[] = 'img/' . $imageDefinition->directory() . '/';
        }
        return $directories;
    }
}
