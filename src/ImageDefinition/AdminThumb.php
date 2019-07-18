<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\ImageDefinition;

final class AdminThumb implements ImageDefinitionInterface
{
    /**
     * @return string
     */
    public static function serviceName(): string
    {
        return 'admin-thumb';
    }

    /**
     * @return int|null
     */
    public function width(): ?int
    {
        return 500;
    }

    /**
     * @return int|null
     */
    public function height(): ?int
    {
        return 500;
    }

    public function upscale(): bool
    {
        return false;
    }

    public function mode(): string
    {
        return ImageDefinitionInterface::MODE_CANVAS;
    }

    /**
     * @return string
     */
    public function directory(): string
    {
        return 'admin-thumb';
    }
}
