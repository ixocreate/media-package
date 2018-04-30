<?php
declare(strict_types=1);

namespace KiwiSuite\Media\ImageDefinition;

interface ImageDefinitionInterface
{
    public static function getName (): string;

    public function getWidth(): ?int;

    public function getHeight(): ?int;

    public function getFit(): bool;

    public function getDirectory(): string;
}