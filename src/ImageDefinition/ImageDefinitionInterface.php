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

    public function getFit(): bool;

    public function getDirectory(): string;
}