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
    const MODE_FIT_DIMENSION = 'fitDimension';

    /**
     * Cut out a rectangular part of the current image with given width and height.
     * Needs width & height
     */
    const MODE_CROP = 'crop';
    /**
     * Adds a canvas to image, if image is smaller than given width & height.
     * Needs width & height.
     * Only recommenden for Thumbnails.
     */
    const MODE_CANVAS = 'canvas';

    public function width(): ?int;

    public function height(): ?int;

    public function mode(): string;

    public function upscale(): bool;

    public function directory(): string;
}