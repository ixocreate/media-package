<?php
/**
 * @see https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\MediaCreateHandler;

use League\Flysystem\FilesystemInterface;

final class TempFileHandler implements MediaCreateHandlerInterface
{
    /**
     * @var string
     */
    private $file;

    /**
     * @var
     */
    private $filename;

    /**
     * TempFileHandler constructor.
     * @param string $file
     * @param string $filename
     */
    public function __construct(string $file, string $filename)
    {
        $this->file = $file;
        $this->filename = $filename;
    }

    public function filename(): string
    {
        return $this->filename;
    }

    public function tempFile(): string
    {
        return $this->file;
    }

    public function move(FilesystemInterface $storage, $destination): bool
    {
        $f = fopen($this->file, 'r');
        $storage->writeStream($destination, $f);

        fclose($f);
        unlink($this->file);

        return true;
    }

    public function mimeType(): string
    {
        $finfo = \finfo_open(FILEINFO_MIME_TYPE);
        return \finfo_file($finfo, $this->file);
    }

    public function fileSize(): int
    {
        return \filesize($this->file);
    }

    public function fileHash(): string
    {
        return \hash_file('sha256', $this->file);
    }
}
