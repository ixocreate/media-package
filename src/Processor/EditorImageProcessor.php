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
use Intervention\Image\Point;
use KiwiSuite\Media\Config\MediaConfig;
use KiwiSuite\Media\Entity\Media;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionInterface;
use KiwiSuite\Media\Repository\MediaRepository;
use Psr\Http\Message\ServerRequestInterface;
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
     * @var array
     */
    private $requestData;

    /**
     * @var ImageDefinitionInterface
     */
    private $imageDefinition;

    /**
     * @var Size
     */
    private $mediaSize;

    /**
     * @var Size
     */
    private $imageDefinitionSize;

    /**
     * @var Size
     */
    private $requestDataSize;

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

        $mediaImageSize = \getimagesize(getcwd() . '/data/media/' . $media->basePath() . $media->filename());
        $this->mediaSize = new Size($mediaImageSize[0], $mediaImageSize[1]);

        $requestDataPoint = new Point($requestData['x'], $requestData['y']);
        $this->requestDataSize = new Size($requestData['width'], $requestData['height'], $requestDataPoint);

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
        $image = $imageManager->make(getcwd() . '/data/media/' . $this->media->basePath() . $this->media->filename());

        $this->gaugeMinimalRequestDataSize();
        $this->gaugeCanvasSize();

        $image->crop($this->requestDataSize->width, $this->requestDataSize->height, $this->requestDataSize->pivot->x, $this->requestDataSize->pivot->y);

        $width = $this->imageDefinition->width();
        $height = $this->imageDefinition->height();

        if ($width === null) {
            $width = $this->requestDataSize->width;
        }
        if ($height === null) {
            $height = $this->requestDataSize->height;
        }

        $image->resize($width, $height, function($constraint) use ($width, $height) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $image->save(\getcwd(). '/data/media/img/' . $this->imageDefinition::serviceName() . '/' . $this->media->basePath() .'2'. $this->media->filename());
        $image->destroy();
    }

    /**
     * Gauges Request Parameters to minimum Size of ImageDefinition
     */
    private function gaugeMinimalRequestDataSize()
    {
        if ($this->requestDataSize->width < $this->imageDefinition->width()) {
            $this->requestDataSize->width = $this->imageDefinition->width();
        }
        if ($this->requestDataSize->height < $this->imageDefinition->height()) {
            $this->requestDataSize->height = $this->imageDefinition->height();
        }
    }

    /**
     * Gauges X and Y Position
     */
    private function gaugeCanvasSize()
    {
        if (($this->requestDataSize->pivot->x + $this->imageDefinition->width()) > $this->mediaSize->width) {
            $diff = ($this->requestDataSize->pivot->x + $this->imageDefinition->width()) - $this->mediaSize->width;
            $this->requestDataSize->pivot->setX($this->requestDataSize->pivot->x - $diff);
        }

        if (($this->requestDataSize->pivot->y + $this->imageDefinition->height()) > $this->mediaSize->height) {
            $diff = ($this->requestDataSize->pivot->y + $this->imageDefinition->height()) - $this->mediaSize->height;
            $this->requestDataSize->pivot->setY($this->requestDataSize->pivot->y - $diff);
        }
    }

    private function zoom()
    {

    }

}