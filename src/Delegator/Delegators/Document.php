<?php
declare(strict_types=1);

namespace KiwiSuite\Media\Delegator\Delegators;

use KiwiSuite\Media\Entity\Media;
use KiwiSuite\Media\Delegator\DelegatorInterface;

final class Document implements DelegatorInterface
{
    /**
     * @var array
     */
    private $allowedMimeTypes = [];

    /**
     * @var array
     */
    private $allowedFileExtensions = [];

    /**
     * @return string
     */
    public static function getName() : string
    {
        return 'Document';
    }

    /**
     * @param Media $media
     */
    public function responsible(Media $media)
    {
        $pathInfo = \pathinfo($media->filename());
        $extension = $pathInfo['extension'];

        if (!\in_array($media->mimeType(), $this->allowedMimeTypes)) {
            return;
        }
        if (!\in_array($extension, $this->allowedFileExtensions)) {
            return;
        }
        $this->process($media);
    }

    /**
     * @param Media $media
     */
    private function process(Media $media)
    {
        // TODO: Implement responsible() method.
    }
}