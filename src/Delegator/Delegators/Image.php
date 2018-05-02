<?php
declare(strict_types=1);

namespace KiwiSuite\Media\Delegator\Delegators;

use KiwiSuite\Media\Entity\Media;
use KiwiSuite\Media\Delegator\DelegatorInterface;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionMapping;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionSubManager;
use Intervention\Image\ImageManager;

final class Image implements DelegatorInterface
{
    /**
     * @var array
     */
    private $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
    ];

    /**
     * @var array
     */
    private $allowedFileExtensions = [
        'jpeg',
        'jpg',
        'jpe',
        'png',
        'gif',
    ];

    /**
     * @var ImageDefinitionMapping : ImageDefinitionInterface
     */
    private $imageDefinitionMapping;

    /**
     * @var ImageDefinitionSubManager
     */
    private $imageDefinitionSubManager;

    /**
     * Image constructor.
     * @param ImageDefinitionMapping $imageDefinitionMapping
     * @param ImageDefinitionSubManager $imageDefinitionSubManager
     */
    public function __construct(ImageDefinitionMapping $imageDefinitionMapping, ImageDefinitionSubManager $imageDefinitionSubManager)
    {
        $this->imageDefinitionMapping = $imageDefinitionMapping;
        $this->imageDefinitionSubManager = $imageDefinitionSubManager;
    }

    /**
     * @return string
     */
    public static function getName() : string
    {
        return 'Image';
    }


    public function responsible(Media $media)
    {
        $pathInfo = \pathinfo($media->filename());
        $extension = $pathInfo['extension'];
        $responsible = true;

        if ((!\in_array($media->mimeType(), $this->allowedMimeTypes)) &&
            (!\in_array($extension, $this->allowedFileExtensions))) {
            $responsible = false;
        }
        if ($responsible === true) {
            $this->process($media);
        }
        return $responsible;
    }

    /**
     * @param Media $media
     */
    private function process(Media $media)
    {
        foreach ($this->imageDefinitionMapping->getMapping() as $imageDefinition) {
            $imageDefinition = $this->imageDefinitionSubManager->get($imageDefinition);
            $imageManager = new ImageManager(['driver' => 'imagick']);

            $width = $imageDefinition->getWidth();
            $height = $imageDefinition->getHeight();
            $fit = $imageDefinition->getFit();
            $directory = trim($imageDefinition->getDirectory(), '/');

            mkdir('data/media/img/'. $directory . '/' . $media->basePath(), 0777, true);
            $image = $imageManager->make('data/media/' . $media->basePath() . $media->filename());

            if ($fit === true) {
                $image->fit($width, $height, function($constraint) {
                    $constraint->upsize();
                });
            } else {
                $image->resize($width, $height, function($constraint) use ($width, $height) {
                    if ($width === null || $height === null) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    }
                });
            }

            $image->save('data/media/img/' . $directory . '/' . $media->basePath() . $media->filename());
            $image->destroy();
        }
    }

}