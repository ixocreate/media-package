<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\CreateHandler;

use Ixocreate\Filesystem\FilesystemInterface;

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
     * @var bool
     */
    private $deleteAfterWrite;

    /**
     * @var string
     */
    private $mimeType = null;

    /**
     * @var int
     */
    private $fileSize = null;

    /**
     * @var string
     */
    private $fileHash = null;

    /**
     * LocalFileHandler constructor.
     * @param string $file
     * @param string $filename
     * @param bool $deleteAfterWrite
     */
    public function __construct(string $file, string $filename, bool $deleteAfterWrite = true)
    {
        $this->file = $file;
        $this->filename = $filename;
        $this->deleteAfterWrite = $deleteAfterWrite;
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
     * @return bool
     */
    public function write(FilesystemInterface $storage, $destination): bool
    {
        $f = \fopen($this->file, 'r');
        $storage->writeStream($destination, $f);

        \fclose($f);
        if ($this->deleteAfterWrite) {
            //get file infos before file is removed;
            $this->mimeType();
            $this->fileSize();
            $this->fileHash();

            \unlink($this->file);
        }

        return true;
    }

    /**
     * @return string
     */
    public function mimeType(): string
    {
        if ($this->mimeType === null) {
            $finfo = \finfo_open(FILEINFO_MIME_TYPE);
            $this->mimeType = \finfo_file($finfo, $this->file);
        }

        return $this->mimeType;
    }

    /**
     * @return int
     */
    public function fileSize(): int
    {
        if ($this->fileSize === null) {
            $this->fileSize = \filesize($this->file);
        }

        return $this->fileSize;
    }

    /**
     * @return string
     */
    public function fileHash(): string
    {
        if ($this->fileHash === null) {
            $this->fileHash = \hash_file('sha256', $this->file);
        }

        return $this->fileHash;
    }
}
