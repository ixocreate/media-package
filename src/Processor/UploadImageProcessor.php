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
        \extract($this->imageParameters);

        $imageManager = new ImageManager(['driver' => $this->mediaConfig->getDriver()]);

        if(!\is_dir($savingDir)) {
            \mkdir($savingDir, 0777, true);
        }
        $image = $imageManager->make($path . $filename);
        $imageWidth = $image->width();
        $imageHeight = $image->height();

        if ($crop === true && $upscale === false) {
            if (!($imageWidth < $width && $imageHeight < $height)) {
                $image->fit($width, $height, function (Constraint $constraint) {
                    $constraint->upsize();
                });
            }
        }

        if ($crop === true && $upscale === true) {
            $image->fit($width, $height, function (Constraint $constraint) use ($width, $height) {
            });
        }

        if ($crop === false && $upscale === true) {
            $image->resize($width, $height, function (Constraint $constraint) use ($width, $height) {
               $constraint->aspectRatio();
            });
        }

        if ($crop === false && $upscale == false) {
            $image->resize($width, $height, function(Constraint $constraint) use ($width, $height) {
                if ($width === null || $height === null) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                }
                $constraint->upsize();
            });
        }

        $image->save($savingDir . $filename);
        $image->destroy();
    }

}