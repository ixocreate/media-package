<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Processor;

use Intervention\Image\ImageManager;
use Ixocreate\Filesystem\FilesystemInterface;
use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\Config\MediaPaths;
use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\ImageDefinition\ImageDefinitionInterface;
use Ixocreate\Media\MediaInterface;

final class EditorProcessor
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
    private $filesystem;

    /**
     * EditorProcessor constructor.
     *
     * @param array $requestData
     * @param ImageDefinitionInterface $imageDefinition
     * @param MediaInterface $media
     * @param MediaConfig $mediaConfig
     * @param FilesystemInterface $filesystem
     */
    public function __construct(
        array $requestData,
        ImageDefinitionInterface $imageDefinition,
        MediaInterface $media,
        MediaConfig $mediaConfig,
        FilesystemInterface $filesystem
    ) {
        $this->requestData = $requestData;
        $this->imageDefinition = $imageDefinition;
        $this->media = $media;
        $this->mediaConfig = $mediaConfig;
        $this->filesystem = $filesystem;
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

        $imageManager = new ImageManager(['driver' => $this->mediaConfig->driver()]);

        $image = $imageManager->make($this->filesystem->read($mediaPath . $this->media->basePath() . $this->media->filename()));

        $this->gaugeMinimalRequestDataSize();
        $this->gaugeCanvasSize($image->getWidth(), $image->getHeight());

        $image->crop($this->requestWidth, $this->requestHeight, $this->requestData['x1'], $this->requestData['y1']);

        (new ImageProcessor(
            $this->media,
            $this->imageDefinition,
            $this->mediaConfig,
            $this->filesystem,
            $image
        ))->process();
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
     *
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
