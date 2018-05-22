<?php

namespace KiwiSuite\Media\Editor;



use Intervention\Image\ImageManager;
use KiwiSuite\Media\Entity\Media;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionInterface;
use KiwiSuite\Media\MediaConfig;


class EditorHandler
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
    private $imgManager;

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
        $this->imgManager = new ImageManager(['driver' => $mediaParameters['driver']]);
        $path = getcwd() . '/data/media/' .  $mediaParameters['basePath'] . $mediaParameters['filename'];
        $this->image = $this->imgManager->make($path);
    }

    public function handle()
    {
        
    }

    private function crop()
    {
        $image->crop(
            $this->requestParameters['x'],
            $this->requestParameters['y'],
            $this->requestParameters['width'],
            $this->requestParameters['height']
        );
        $image->resize(
            $this->imageDefinitionParameters['width'],
            $this->imageDefinitionParameters['height']
        );
        $image->save(getcwd() . '/data/cityResize.jpg');
        $image->destroy();
    }


    private function checkSelectBoxSize()
    {
        $minSelectBoxSizeWidth = $this->imageDefinitionParameters['width'];
        $minSelectBoxSizeHeight = $this->imageDefinitionParameters['height'];

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

    private function checkCanvas()
    {
        $positionX = $this->requestParameters['x'];
        $positionY = $this->requestParameters['y'];

        $maxWidth = $this->canvas['width'];
        $maxHeight = $this->canvas['height'];

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
            floor(($this->imageDefinitionParameters['width'] / $this->imageDefinitionParameters['height']) * 100) / 100;
        return $factor;

    }


}