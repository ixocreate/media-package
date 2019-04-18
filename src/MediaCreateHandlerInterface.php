<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Package\Media;

use League\Flysystem\FilesystemInterface;

interface MediaCreateHandlerInterface
{
    public function filename(): string;

    public function tempFile(): string;

    public function mimeType(): string;

    public function fileSize(): int;

    public function fileHash(): string;

    public function move(FilesystemInterface $storage, $destination);
}
