<?php
/**
 * @see https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\MediaCreateHandler;

use League\Flysystem\FilesystemInterface;
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
     * @param UploadedFile $uploadedFile
     */
    public function __construct(UploadedFile $uploadedFile)
    {
        $this->uploadedFile = $uploadedFile;
        $this->stream = $this->uploadedFile->getStream()->detach();
    }

    public function filename(): string
    {
        return $this->uploadedFile->getClientFilename();
    }

    public function tempFile(): string
    {
        return $this->uploadedFile->getStream()->getMetadata()['uri'];
    }

    public function mimeType(): string
    {
        return $this->uploadedFile->getClientMediaType();
    }

    public function fileSize(): int
    {
        return $this->uploadedFile->getSize();
    }

    public function fileHash(): string
    {
        $hashInit = hash_init('sha256');
        \hash_update_stream($hashInit, $this->stream);
        return hash_final($hashInit);
    }

    public function move(FilesystemInterface $storage, $destination)
    {
        return $storage->writeStream($destination, $this->stream);
    }
}
