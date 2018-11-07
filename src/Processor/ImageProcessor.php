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

namespace KiwiSuite\Media\Processor;

use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use KiwiSuite\Media\Config\MediaConfig;
use Intervention\Image\Size;
use Intervention\Image\Constraint;
use KiwiSuite\Media\Entity\Media;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionInterface;

final class ImageProcessor implements ProcessorInterface
{
    /**
     * @var array
     */
    private $imageParameters = [];

    /**
     * @var Media
     */
    private $media;

    /**
     * @var ImageDefinitionInterface
     */
    private $imageDefinition;

    /**
     * @var MediaConfig
     */
    private $mediaConfig;

    /**
     * @var Image
     */
    private $image;

    /**
     * ImageProcessor constructor.
     * @param Media $media
     * @param ImageDefinitionInterface $imageDefinition
     * @param MediaConfig $mediaConfig
     * @param Image|null $image
     */
    public function __construct(Media $media,ImageDefinitionInterface $imageDefinition, MediaConfig $mediaConfig, Image $image = null)
    {
        $this->media = $media;
        $this->imageDefinition = $imageDefinition;
        $this->mediaConfig = $mediaConfig;
        $this->image = $image;
        $this->setParameters();
    }

    /**
     * @return string
     */
    public static function serviceName(): string
    {
        return 'ImageProcessor';
    }

    private function setParameters()
    {
        $this->imageParameters = [
            'imagePath'      => 'data/media/' . $this->media->basePath(),
            'imageFilename'  => $this->media->filename(),
            'definitionSavingDir' => 'data/media/img/'. $this->imageDefinition->directory() . '/' . $this->media->basePath(),
            'definitionWidth'     => $this->imageDefinition->width(),
            'definitionHeight'    => $this->imageDefinition->height(),
            'definitionMode'      => $this->imageDefinition->mode(),
            'definitionUpscale'   => $this->imageDefinition->upscale()
        ];
    }

    /**
     * Processes UploadAction Images
     */
    public function process()
    {
        $imageManager = new ImageManager(['driver' => $this->mediaConfig->driver()]);

        if(!\is_dir($this->imageParameters['definitionSavingDir'])) {
            \mkdir($this->imageParameters['definitionSavingDir'],0777, true);
        }
        $image = ($this->image != null) ? $this->image : $imageManager->make($this->imageParameters['imagePath'] . $this->imageParameters['imageFilename']);

        $this->imageParameters['imageWidth'] = $image->width();
        $this->imageParameters['imageHeight'] = $image->height();

        $this->checkMode($image, $this->imageParameters);

        $image->save($this->imageParameters['definitionSavingDir'] . $this->imageParameters['imageFilename']);
        $image->destroy();
    }

    /**
     * @param Image $image
     * @param array $imageParameters
     */
    private function checkMode(Image $image, array $imageParameters)
    {
        switch ($imageParameters['definitionMode']) {
            case ('fit'):
                $this->fit($image, $imageParameters);
                break;
            case ('fitCrop'):
                $this->fitCrop($image, $imageParameters);
                break;
            case ('canvas'):
                $this->canvas($image, $imageParameters);
                break;
            case ('canvasFitCrop'):
                $this->canvasFitCrop($image, $imageParameters);
                break;
        }
    }

    /**
     * @param Image $image
     * @param array $imageParameters
     */
    private function fit(Image $image, array $imageParameters)
    {
        \extract($imageParameters);


        $image->resize($definitionWidth,$definitionHeight, function (Constraint $constraint) use ($definitionWidth, $definitionHeight, $definitionUpscale) {
            if ($definitionUpscale === false) {
                $constraint->upsize();
            }
            $constraint->aspectRatio();
        });
    }

    /**
     * @param Image $image
     * @param array $imageParameters
     */
    private function fitCrop(Image $image, array $imageParameters)
    {
        \extract($imageParameters);

        if ($definitionWidth != null && $definitionHeight != null) {
            $image->fit($definitionWidth, $definitionHeight, function (Constraint $constraint) use ($definitionWidth, $definitionHeight, $definitionUpscale) {
                if ($definitionUpscale === false) {
                    $constraint->upsize();
                }
            });
        }
    }

    /**
     * @param Image $image
     * @param array $imageParameters
     */
    private function canvas(Image $image, array $imageParameters)
    {
        \extract($imageParameters);

        $image->resize($definitionWidth, $definitionHeight, function (Constraint $constraint) use ($definitionWidth, $definitionHeight, $definitionUpscale) {
            if ($definitionUpscale === false) {
                $constraint->upsize();
            }
            $constraint->aspectRatio();
        });

        if (($image->width() != $definitionWidth) || ($image->height() != $definitionHeight)) {
            $image->resizeCanvas($definitionWidth,$definitionHeight);
        }

    }

    /**
     * @param Image $image
     * @param array $imageParameters
     */
    private function canvasFitCrop(Image $image, array $imageParameters)
    {
        \extract($imageParameters);

        if ($imageWidth >= $definitionWidth && $imageHeight >= $definitionHeight) {
            $image->fit($definitionWidth, $definitionHeight);
        } elseif ($imageWidth >= $definitionWidth || $imageHeight >= $definitionHeight) {
            $image->crop($definitionWidth,$definitionHeight);
        }

        if (($image->width() != $definitionWidth) || ($image->height() != $definitionHeight)) {
            $image->resizeCanvas($definitionWidth,$definitionHeight);
        }

    }

}