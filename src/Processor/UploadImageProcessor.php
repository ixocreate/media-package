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
            \mkdir($this->imageParameters['definitionSavingDir'], 0777, true);
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
            case ('fitDimension'):
                $this->fitDimension($image, $imageParameters);
                break;
            case ('crop'):
                $this->crop($image, $imageParameters);
                break;
            case ('canvas'):
                $this->canvas($image, $imageParameters);
                break;
        }
    }

    private function fitDimension(Image $image, array $imageParameters)
    {
        \extract($imageParameters);


        if ($definitionWidth === null || $definitionHeight === null) {
            if ($definitionWidth != null) {
                $imageFactor = $this->checkFactor($imageWidth, $imageHeight);
                $definitionHeight = $this->getMissingHeight($definitionWidth, $imageFactor);
                $image->resize($definitionWidth,$definitionHeight, function (Constraint $constraint) use ($definitionWidth, $definitionHeight, $definitionUpscale) {
                   if ($definitionUpscale === false) {
                       $constraint->upsize();
                   }
                });
                return;
            }
            if ($definitionHeight != null) {
                $imageFactor = $this->checkFactor($imageHeight, $imageWidth);
                $definitionWidth = $this->getMissingWidth($definitionHeight, $imageFactor);
                $image->resize($definitionWidth, $definitionHeight, function (Constraint $constraint) use ($definitionWidth, $definitionHeight, $definitionUpscale) {
                   if ($definitionUpscale === false) {
                       $constraint->upsize();
                   }
                });
            }
            return;
        }

        if ($definitionUpscale === false) {
            if ($imageWidth < $definitionWidth && $imageHeight < $definitionHeight) {
                return;
            }
        }

        if ($imageWidth > $imageHeight) {
            $imageFactor = $imageWidth / $imageHeight;
            $newWidth = $definitionWidth;
            $newHeight = round($newWidth / $imageFactor);
        } elseif ($imageWidth < $imageHeight) {
            $imageFactor = $imageHeight / $imageWidth;
            $newHeight = $definitionHeight;
            $newWidth = round($newHeight / $imageFactor);
        } elseif ($imageWidth == $imageHeight) {
            if ($definitionWidth < $definitionHeight) {
                $newWidth = $definitionWidth;
                $newHeight = $definitionWidth;
            } else {
                $newWidth = $definitionHeight;
                $newHeight = $definitionHeight;
            }
        }

        $image->resize($newWidth, $newHeight, function(Constraint $constraint) use ($newWidth, $newHeight, $definitionUpscale)
        {
            if ($definitionUpscale === false) {
                $constraint->upsize();
            }
        });

    }

    private function crop(Image $image, array $imageParameters)
    {
        \extract($imageParameters);

        if ($definitionWidth != null && $definitionHeight != null) {
            $image->crop($definitionWidth, $definitionHeight);
        }
    }

    private function canvas(Image $image, array $imageParameters)
    {
        \extract($imageParameters);

        if ($imageWidth < $definitionWidth && $imageHeight < $definitionHeight) {
            $image->resizeCanvas($definitionWidth, $definitionHeight);
        } else {
            $image->resize($definitionWidth, $definitionHeight, function (Constraint $constraint) use ($definitionWidth, $definitionHeight) {
                $constraint->upsize();
                $constraint->aspectRatio();
            });
        }

    }

    private function checkFactor($value1, $value2)
    {
        return $value1 / $value2;
    }

    private function getMissingHeight($width, $imageFactor)
    {
        return \round($width / $imageFactor);
    }

    private function getMissingWidth($height, $imageFactor)
    {
        return \round($height * $imageFactor);
    }

}