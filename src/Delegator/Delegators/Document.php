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
        // TODO: Implement responsible() method.
    }
}