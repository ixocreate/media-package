<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Misc\Media;

use Ixocreate\Media\ImageDefinitionInterface;

final class ImageDefinitionMock implements ImageDefinitionInterface
{
    public function width(): ?int
    {
        return 100;
    }

    public function height(): ?int
    {
        return 100;
    }

    public function mode(): string
    {
        return ImageDefinitionInterface::MODE_FIT;
    }

    public function upscale(): bool
    {
        return false;
    }

    public function directory(): string
    {
        return 'foo';
    }

    public static function serviceName(): string
    {
        return 'foo';
    }
}
