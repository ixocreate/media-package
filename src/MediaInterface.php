<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Package;

interface MediaInterface
{
    public function basePath(): string;

    public function filename(): string;

    public function mimeType(): string;

    public function size(): int;

    public function publicStatus(): bool;

    public function hash(): string;
}
