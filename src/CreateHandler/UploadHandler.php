<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\CreateHandler;

use Ixocreate\Filesystem\FilesystemInterface;
use Ixocreate\Filesystem\Settings;
use Laminas\Diactoros\UploadedFile;
use League\Flysystem\Config;
use League\Flysystem\Visibility;

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
     * @var string
     */
    private $mimeType = null;

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
        if ($this->mimeType === null) {
            $finfo = \finfo_open(FILEINFO_MIME_TYPE);
            $this->mimeType = \finfo_file($finfo, $this->tempFile());
        }

        return $this->mimeType;
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
     * @return bool
     */
    public function write(FilesystemInterface $storage, $destination, bool $isPublic = true)
    {
        $this->mimeType();

        $visibility = $isPublic ? Visibility::PUBLIC : Visibility::PRIVATE;
        $storage->writeStream($destination, $this->stream, new Settings([Config::OPTION_DIRECTORY_VISIBILITY => $visibility, Config::OPTION_VISIBILITY => $visibility]));

        return true;
    }
}
