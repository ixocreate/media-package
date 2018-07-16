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
    public function getWidth(): ?int;

    public function getHeight(): ?int;

    /**
     * If Crop is set to true, overhanging Image parts will be cropped away.
     * @return bool
     */
    public function getCrop(): bool;

    /**
     * If Upscale is set to true and the Image width & height is smaller than given width & height, it will be upscaled.
     * If Upscale is set to false and the Image width & height is smaller than given width & height, it will stay the same.
     * @return bool
     */
    public function getUpscale(): bool;

    /**
     * Adds a canvas to image, if image is smaller than given width & height.
     * Only recommenden for Thumbnails.
     * @return bool
     */
    public function getCanvas(): bool;

    public function getDirectory(): string;
}