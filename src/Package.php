<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media;

use Ixocreate\Application\Package\PackageInterface;
use Ixocreate\Media\Handler\MediaHandlerBootstrapItem;
use Ixocreate\Media\ImageDefinition\ImageDefinitionBootstrapItem;

final class Package implements PackageInterface
{
    /**
     * @return array
     */
    public function getBootstrapItems(): array
    {
        return [
            MediaBootstrapItem::class,
            MediaHandlerBootstrapItem::class,
            ImageDefinitionBootstrapItem::class,
        ];
    }

    /**
     * @return null|string
     */
    public function getBootstrapDirectory(): ?string
    {
        return __DIR__ . '/../bootstrap';
    }

    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return [];
    }
}
