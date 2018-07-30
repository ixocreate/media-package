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

final class UploadImageProcessor
{
    /**
     * @var array
     */
    private $imageParameters = [];

    /**
     * @var MediaConfig
     */
    private $mediaConfig;

    /**
     * ImageProcessor constructor.
     * @param array $imageParameters
     * @param MediaConfig $mediaConfig
     */
    public function __construct(array $imageParameters, MediaConfig $mediaConfig)
    {
        $this->imageParameters = $imageParameters;
        $this->mediaConfig = $mediaConfig;
    }

    /**
     * Processes UploadAction Images
     */
    public function process()
    {
        $imageManager = new ImageManager(['driver' => $this->mediaConfig->getDriver()]);

        if(!\is_dir($this->imageParameters['definitionSavingDir'])) {
            \mkdir($this->imageParameters['definitionSavingDir'],0777, true);
        }
        $image = $imageManager->make($this->imageParameters['imagePath'] . $this->imageParameters['imageFilename']);

        $this->imageParameters['imageWidth'] = $image->width();
        $this->imageParameters['imageHeight'] = $image->height();

        $this->checkMode($image, $this->imageParameters);

        $image->save($this->imageParameters['definitionSavingDir'] . $this->imageParameters['imageFilename']);
        $image->destroy();
    }

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