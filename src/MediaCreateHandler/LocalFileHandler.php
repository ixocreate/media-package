<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Package\MediaCreateHandler;

use Ixocreate\Media\Package\MediaCreateHandlerInterface;
use League\Flysystem\FilesystemInterface;

final class LocalFileHandler implements MediaCreateHandlerInterface
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

    /**
     * @return string
     */
    public function filename(): string
    {
        return $this->filename;
    }

    /**
     * @return string
     */
    public function tempFile(): string
    {
        return $this->file;
    }

    /**
     * @param FilesystemInterface $storage
     * @param $destination
     * @throws \League\Flysystem\FileExistsException
     * @return bool
     */
    public function move(FilesystemInterface $storage, $destination): bool
    {
        $f = \fopen($this->file, 'r');
        $storage->writeStream($destination, $f);

        \fclose($f);
        \unlink($this->file);

        return true;
    }

    /**
     * @return string
     */
    public function mimeType(): string
    {
        $finfo = \finfo_open(FILEINFO_MIME_TYPE);
        return \finfo_file($finfo, $this->file);
    }

    /**
     * @return int
     */
    public function fileSize(): int
    {
        return \filesize($this->file);
    }

    /**
     * @return string
     */
    public function fileHash(): string
    {
        return \hash_file('sha256', $this->file);
    }
}
