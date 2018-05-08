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
use KiwiSuite\Media\ImageDefinition\ImageDefinitionMapping;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionSubManager;
use Intervention\Image\ImageManager;
use KiwiSuite\Media\MediaConfig;

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
     * @var ImageDefinitionMapping : ImageDefinitionInterface
     */
    private $imageDefinitionMapping;

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
    public function __construct(ImageDefinitionMapping $imageDefinitionMapping, ImageDefinitionSubManager $imageDefinitionSubManager, MediaConfig $mediaConfig)
    {
        $this->imageDefinitionMapping = $imageDefinitionMapping;
        $this->imageDefinitionSubManager = $imageDefinitionSubManager;
        $this->mediaConfig = $mediaConfig;
    }

    /**
     * @return string
     */
    public static function getName() : string
    {
        return 'Image';
    }


    public function responsible(Media $media)
    {
        $pathInfo = \pathinfo($media->filename());
        $extension = $pathInfo['extension'];
        $responsible = true;

        if ((!\in_array($media->mimeType(), $this->allowedMimeTypes)) &&
            (!\in_array($extension, $this->allowedFileExtensions))) {
            $responsible = false;
        }
        if ($responsible === true) {
            $this->process($media);
        }
        return $responsible;
    }

    /**
     * @param Media $media
     */
    private function process(Media $media)
    {
        $imageManager = new ImageManager(['driver' => $this->mediaConfig->getDriver()]);

        foreach ($this->imageDefinitionMapping->getMapping() as $imageDefinition) {
            $imageDefinition = $this->imageDefinitionSubManager->get($imageDefinition);

            $width = $imageDefinition->getWidth();
            $height = $imageDefinition->getHeight();
            $fit = $imageDefinition->getFit();
            $directory = trim($imageDefinition->getDirectory(), '/');

            mkdir('data/media/img/'. $directory . '/' . $media->basePath(), 0777, true);
            $image = $imageManager->make('data/media/' . $media->basePath() . $media->filename());

            if ($fit === true) {
                $image->fit($width, $height, function($constraint) {
                    $constraint->upsize();
                });
            } else {
                $image->resize($width, $height, function($constraint) use ($width, $height) {
                    if ($width === null || $height === null) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    }
                });
            }

            $image->save('data/media/img/' . $directory . '/' . $media->basePath() . $media->filename());
            $image->destroy();
        }
    }

}