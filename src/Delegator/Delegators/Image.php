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

namespace KiwiSuite\Media\Delegator\Delegators;

use KiwiSuite\Config\Config;
use KiwiSuite\Media\Entity\Media;
use KiwiSuite\Media\Delegator\DelegatorInterface;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionInterface;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionMapping;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionSubManager;
use Intervention\Image\ImageManager;
use KiwiSuite\Media\Processor\ImageProcessor;
use KiwiSuite\Media\Config\MediaConfig;
use Zend\Diactoros\UploadedFile;

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
     * @param ImageDefinitionMapping $imageDefinitionMapping
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
        foreach ($this->imageDefinitionSubManager->getServiceManagerConfig()->getNamedServices() as $name => $imageDefinition) {
            /** @var ImageDefinitionInterface $imageDefinition */
            $imageDefinition = $this->imageDefinitionSubManager->get($imageDefinition);

            $imageProcessor = new ImageProcessor($media, $imageDefinition, $this->mediaConfig);
            $imageProcessor->process();
        }
    }
}