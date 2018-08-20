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


final class EditorImageProcessor implements ProcessorInterface
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
        $path = \getcwd() . '/data/media/' .  $mediaParameters['basePath'] . $mediaParameters['filename'];
        $this->image = $this->imageManager->make($path);
    }

    public static function serviceName(): string
    {
        return 'EditorImageProcessor';
    }

    /**
     * Processes the recieved action
     * @throws Exception
     */
    public function process()
    {
        $this->validateRequest();
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
            \getcwd() .
            '/data/media/img/' .
            $this->imageDefinitionParameters['directory'] .
            '/' .
            $this->mediaParameters['basePath'] .
            $this->mediaParameters['filename']
        );
        $this->image->destroy();
    }

    /**
     * Validates the incoming request parameters
     * @throws Exception
     */
    private function validateRequest()
    {
        $this->checkSelectBoxSize();
        $this->checkFactor();
        $this->checkCanvas();
    }

    /**
     * @throws Exception
     */
    private function checkSelectBoxSize()
    {
        if ($this->imageDefinitionParameters['width'] === null && $this->imageDefinitionParameters['height'] === null) {
            throw new Exception(
                "Neither 'width' or 'height' is defined in the ImageDefinition, atleast one needs to be set."
            );
        }

        if ($this->imageDefinitionParameters['width'] === null || $this->imageDefinitionParameters['height'] === null) {
            $this->checkAutoSelectBoxSize();
            return;
        }
        $this->gaugeMinBoxSize();
    }

    /**
     * Gauges the allowed minimum select size
     */
    private function gaugeMinBoxSize()
    {
        if ($this->requestParameters['width'] < $this->imageDefinitionParameters['width']) {
            $this->requestParameters['width'] = $this->imageDefinitionParameters['width'];
        }

        if ($this->requestParameters['height'] < $this->imageDefinitionParameters['height']) {
            $this->requestParameters['height'] = $this->imageDefinitionParameters['height'];
        }
    }

    /**
     * Gauges the allowed minimum select size, when null is given in an ImageDefinition
     */
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

    /**
     * Gauges the X and Y position
     */
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

    /**
     * @throws Exception
     */
    private function checkFactor()
    {
        $imageDefinitionFactor =
            \floor(
                ($this->imageDefinitionParameters['width'] / $this->imageDefinitionParameters['height']) * 100
            ) / 100;
        $selectBoxFactor =
            \floor(($this->requestParameters['width'] / $this->requestParameters['height']) * 100) / 100;

        if ($imageDefinitionFactor != $selectBoxFactor) {
            throw new Exception('Size doesnt conform ImageDefinition factor');
        }

    }


}