<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\MediaCreateHandler;

use Ixocreate\Filesystem\FilesystemInterface;
use Ixocreate\Media\MediaCreateHandlerInterface;
use Zend\Diactoros\UploadedFile;

final class UploadHandler implements MediaCreateHandlerInterface
{
    /**
     * @var UploadedFile
     */
    private $uploadedFile;

    /**
     * @var resource|null
     */
    private $stream;

    /**
     * UploadHandler constructor.
     *
     * @param UploadedFile $uploadedFile
     */
    public function __construct(UploadedFile $uploadedFile)
    {
        $this->uploadedFile = $uploadedFile;
        $this->stream = $this->uploadedFile->getStream()->detach();
    }

    /**
     * @return string
     */
    public function filename(): string
    {
        return $this->uploadedFile->getClientFilename();
    }

    /**
     * @return string
     */
    public function tempFile(): string
    {
        $metadata = \stream_get_meta_data($this->stream);
        return $metadata['uri'];
    }

    /**
     * @return string
     */
    public function mimeType(): string
    {
        return $this->uploadedFile->getClientMediaType();
    }

    /**
     * @return int
     */
    public function fileSize(): int
    {
        return $this->uploadedFile->getSize();
    }

    /**
     * @return string
     */
    public function fileHash(): string
    {
        $hashInit = \hash_init('sha256');
        \hash_update_stream($hashInit, $this->stream);
        return \hash_final($hashInit);
    }

    /**
     * @param FilesystemInterface $storage
     * @param $destination
     * @throws \League\Flysystem\FileExistsException
     * @return bool
     */
    public function move(FilesystemInterface $storage, $destination)
    {
        return $storage->writeStream($destination, $this->stream);
    }
}
