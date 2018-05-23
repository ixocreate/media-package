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
use Intervention\Image\Size;
use KiwiSuite\Media\Entity\Media;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionInterface;
use Exception;


final class EditorProcessor
{
    /**
     * @var array
     */
    private $requestParameters = [];

    /**
     * @var array
     */
    private $imageDefinitionParameters = [];

    /**
     * @var array
     */
    private $mediaParameters = [];

    /**
     * @var ImageManager
     */
    private $imageManager;

    /**
     * @var \Intervention\Image\Image
     */
    private $image;

    /**
     * EditorHandler constructor.
     * @param array $requestParameters
     * @param array $mediaParameters
     * @param array $imageDefinitionParameters
     */
    public function __construct(array $requestParameters, array $mediaParameters, array $imageDefinitionParameters)
    {
        $this->requestParameters = $requestParameters;
        $this->mediaParameters = $mediaParameters;
        $this->imageDefinitionParameters = $imageDefinitionParameters;
        $this->imageManager = new ImageManager(['driver' => $mediaParameters['driver']]);
        $path = getcwd() . '/data/media/' .  $mediaParameters['basePath'] . $mediaParameters['filename'];
        $this->image = $this->imageManager->make($path);
    }

    public function process()
    {
        $this->checkSelectBoxSize();
        $this->checkCanvas();
        
        $width = $this->imageDefinitionParameters['width'];
        $height = $this->imageDefinitionParameters['height'];

        $this->image->crop(
            $this->requestParameters['width'],
            $this->requestParameters['height'],
            $this->requestParameters['x'],
            $this->requestParameters['y']
        );

        $this->image->resize($width, $height, function($constraint) use ($width, $height) {
            if ($width === null || $height === null) {
                $constraint->aspectRatio();
                $constraint->upsize();
            }
        });

        $this->image->save(
            getcwd() .
            '/data/media/img/' .
            $this->imageDefinitionParameters['directory'] .
            '/' .
            $this->mediaParameters['basePath'] .
            $this->mediaParameters['filename']
        );
        $this->image->destroy();
    }
    
    private function checkSelectBoxSize()
    {
        $minSelectBoxSizeWidth = $this->imageDefinitionParameters['width'];
        $minSelectBoxSizeHeight = $this->imageDefinitionParameters['height'];

        if ($minSelectBoxSizeWidth === null && $minSelectBoxSizeHeight === null) {
            throw new Exception(
                "Neither 'width' or 'height' is defined in the ImageDefinition, atleast one needs to be set."
            );
        }

        if ($minSelectBoxSizeWidth === null || $minSelectBoxSizeHeight === null) {
            $this->checkAutoSelectBoxSize();
        } else {
            if ($this->requestParameters['width'] < $minSelectBoxSizeWidth) {
                $this->requestParameters['width'] = $minSelectBoxSizeWidth;
            }
            if ($this->requestParameters['height'] < $minSelectBoxSizeHeight) {
                $this->requestParameters['height'] = $minSelectBoxSizeHeight;
            }

            $currentSelectBoxSizeFactor =
                floor(($this->requestParameters['width'] / $this->requestParameters['height']) * 100) / 100;

            $checkfactor = $this->checkFactor();

            if ($currentSelectBoxSizeFactor != $checkfactor) {
                throw new Exception('Size doesnt conform ImageDefinition factor');
            }
        }
    }

    private function checkAutoSelectBoxSize()
    {
        if ($this->imageDefinitionParameters['width'] === null) {
            $minSize = $this->imageDefinitionParameters['height'];
            if ($this->requestParameters['height'] < $minSize) {
                $this->requestParameters['height'] = $minSize;
            }
        }

        if ($this->imageDefinitionParameters['height'] === null) {
            $minSize = $this->imageDefinitionParameters['width'];
            if ($this->requestParameters['width'] < $minSize) {
                $this->requestParameters['width'] = $minSize;
            }
        }
    }

    private function checkCanvas()
    {
        $positionX = $this->requestParameters['x'];
        $positionY = $this->requestParameters['y'];

        $maxWidth = $this->mediaParameters['width'];
        $maxHeight = $this->mediaParameters['height'];

        $width = $this->requestParameters['width'];
        $height = $this->requestParameters['height'];

        if (($positionX + $width) > $maxWidth) {
            $diff = ($positionX + $width) - $maxWidth;
            $this->requestParameters['x'] = $positionX - $diff;
        }

        if (($positionY + $height) > $maxHeight) {
            $diff = ($positionY + $height) - $maxHeight;
            $this->requestParameters['y'] = $positionY - $diff;
        }
    }

    private function checkFactor()
    {
        $factor = null;
        $factor =
            floor(
                ($this->imageDefinitionParameters['width'] / $this->imageDefinitionParameters['height']) * 100
            ) / 100;
        return $factor;

    }


}