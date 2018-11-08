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
use KiwiSuite\Media\Entity\Media;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionInterface;
use Intervention\Image\Size;

final class EditorImageProcessor implements ProcessorInterface
{
    /**
     * @var Media
     */
    private $media;

    /**
     * @var MediaConfig
     */
    private $mediaConfig;

    /**
     * @var ImageDefinitionInterface
     */
    private $imageDefinition;

    /**
     * @var Size
     */
    private $mediaSize;

    /**
     * @var array
     */
    private $requestData;

    /**
     * @var mixed
     */
    private $requestWidth;

    /**
     * @var mixed
     */
    private $requestHeight;

    /**
     * NewEditorImageProcessor constructor.
     * @param array $requestData
     * @param ImageDefinitionInterface $imageDefinition
     * @param Media $media
     * @param MediaConfig $mediaConfig
     */
    public function __construct(array $requestData, ImageDefinitionInterface $imageDefinition, Media $media, MediaConfig $mediaConfig)
    {
        $this->imageDefinition = $imageDefinition;
        $this->mediaConfig = $mediaConfig;
        $this->media = $media;
        $this->requestData = $requestData;

        $mediaImageSize = \getimagesize(\getcwd() . '/data/media/' . $media->basePath() . $media->filename());
        $this->mediaSize = new Size($mediaImageSize[0], $mediaImageSize[1]);

        $this->requestWidth = $requestData['x2'] - $requestData['x1'];
        $this->requestHeight = $requestData['y2'] - $requestData['y1'];
    }

    /**
     * @return string
     */
    public static function serviceName(): string
    {
        return 'NewEditorImageProcessor';
    }

    /**
     *
     */
    public function process()
    {
        $imageManager = new ImageManager(['driver' => $this->mediaConfig->driver()]);
        $image = $imageManager->make(\getcwd() . '/data/media/' . $this->media->basePath() . $this->media->filename());

        $this->gaugeMinimalRequestDataSize();
        $this->gaugeCanvasSize();

        $image->crop($this->requestWidth, $this->requestHeight, $this->requestData['x1'], $this->requestData['y1']);

        (new ImageProcessor($this->media, $this->imageDefinition, $this->mediaConfig, $image))->process();
    }

    /**
     * Gauges Request Parameters to minimum Size of ImageDefinition
     */
    private function gaugeMinimalRequestDataSize()
    {
        $this->requestWidth =
            ($this->requestWidth < $this->imageDefinition->width()) ? $this->imageDefinition->width() : $this->requestWidth;
        $this->requestHeight =
            ($this->requestHeight < $this->imageDefinition->height()) ? $this->imageDefinition->height() : $this->requestHeight;
    }

    /**
     * Gauges X and Y Position
     */
    private function gaugeCanvasSize()
    {
        if (($this->requestData['x1'] + $this->requestWidth) > $this->mediaSize->width) {
            $diff = ($this->requestData['x1'] + $this->requestWidth) - $this->mediaSize->width;
            $this->requestData['x1'] = $this->requestData['x1'] - $diff;
        }

        if (($this->requestData['y1'] + $this->requestHeight) > $this->mediaSize->height) {
            $diff = ($this->requestData['y1'] + $this->requestHeight) - $this->mediaSize->height;
            $this->requestData['y1'] = $this->requestData['y1'] - $diff;
        }
    }

    private function zoom()
    {
        //TODO
    }
}
