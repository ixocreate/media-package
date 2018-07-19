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

namespace KiwiSuite\Media\ImageDefinition;

use KiwiSuite\Contract\ServiceManager\NamedServiceInterface;

interface ImageDefinitionInterface extends NamedServiceInterface
{
    /**
     * Fits an image into given definiton.
     */
    const MODE_FIT = 'fit';

    /**
     * Combine cropping and resizing to format image.
     * The method will find the best fitting aspect ratio of your given width and height on the current image automatically,
     * cut it out and resize it to the given dimension
     */
    const MODE_FIT_CROP = 'fitCrop';
    /**
     * Adds a canvas to image, if image is smaller than given width & height, else just resizes to given parameters.
     * Needs width & height.
     * Only recommenden for Thumbnails.
     */
    const MODE_CANVAS = 'canvas';

    /**
     * Combination of MODE_FIT_CROP & MODE_CANVAS
     * Needs width & height.
     */
    const MODE_CANVAS_FIT_CROP = 'canvasFitCrop';

    public function width(): ?int;

    public function height(): ?int;

    public function mode(): string;

    public function upscale(): bool;

    public function directory(): string;
}