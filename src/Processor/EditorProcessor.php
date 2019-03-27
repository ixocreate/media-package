<?php
/**
 * @see https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Processor;

use Intervention\Image\ImageManager;
use Ixocreate\Contract\Media\ImageDefinitionInterface;
use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\Entity\Media;
use Intervention\Image\Size;
use Ixocreate\Media\MediaPaths;
use League\Flysystem\FilesystemInterface;

final class EditorProcessor implements ProcessorInterface
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
     * @var FilesystemInterface
     */
    private $storage;

    /**
     * EditorProcessor constructor.
     * @param array $requestData
     * @param ImageDefinitionInterface $imageDefinition
     * @param Media $media
     * @param MediaConfig $mediaConfig
     * @param FilesystemInterface $storage
     */
    public function __construct(
        array $requestData,
        ImageDefinitionInterface $imageDefinition,
        Media $media,
        MediaConfig $mediaConfig,
        FilesystemInterface $storage
    )
    {
        $this->requestData = $requestData;
        $this->imageDefinition = $imageDefinition;
        $this->media = $media;
        $this->mediaConfig = $mediaConfig;
        $this->storage = $storage;
        $this->requestWidth = $requestData['x2'] - $requestData['x1'];
        $this->requestHeight = $requestData['y2'] - $requestData['y1'];
    }

    /**
     * @return string
     */
    public static function serviceName(): string
    {
        return 'EditorProcessor';
    }

    /**
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function process()
    {

        $mediaPath = $this->media->publicStatus() ? MediaPaths::PUBLIC_PATH : MediaPaths::PRIVATE_PATH;

        $file = $this->storage->read($mediaPath . $this->media->basePath() . $this->media->filename());

        $size = \getimagesizefromstring($file);

        $imageWidth = $size[0];
        $imageHeight = $size[1];

        $imageManager = new ImageManager(['driver' => $this->mediaConfig->driver()]);

        $image = $imageManager->make($this->storage->read($mediaPath . $this->media->basePath() . $this->media->filename()));

        $this->gaugeMinimalRequestDataSize();
        $this->gaugeCanvasSize($imageWidth, $imageHeight);

        $image->crop($this->requestWidth, $this->requestHeight, $this->requestData['x1'], $this->requestData['y1']);

        (new ImageProcessor($this->media, $this->imageDefinition, $this->mediaConfig, $this->storage, $image))->process();
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
     * @param $imageWidth
     * @param $imageHeight
     */
    private function gaugeCanvasSize($imageWidth, $imageHeight)
    {
        if (($this->requestData['x1'] + $this->requestWidth) > $imageWidth) {
            $diff = ($this->requestData['x1'] + $this->requestWidth) - $imageWidth;
            $this->requestData['x1'] = $this->requestData['x1'] - $diff;
        }

        if (($this->requestData['y1'] + $this->requestHeight) > $imageHeight) {
            $diff = ($this->requestData['y1'] + $this->requestHeight) - $imageHeight;
            $this->requestData['y1'] = $this->requestData['y1'] - $diff;
        }
    }
}
