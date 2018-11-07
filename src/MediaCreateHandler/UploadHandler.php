<?php

declare(strict_types=1);

namespace KiwiSuite\Media\MediaCreateHandler;

use Zend\Diactoros\UploadedFile;

class UploadHandler implements MediaCreateHandlerInterface
{
    /**
     * @var UploadedFile
     */
    private $uploadedFile;

    /**
     * UploadHandler constructor.
     * @param UploadedFile $uploadedFile
     */
    public function __construct(UploadedFile $uploadedFile)
    {
        $this->uploadedFile = $uploadedFile;
    }

    public function filename(): string
    {
        return $this->uploadedFile->getClientFilename();
    }

    public function tempFile(): string
    {
        return $this->uploadedFile->getStream()->getMetadata()['uri'];
    }

    public function move($destination): bool
    {
        $this->uploadedFile->moveTo($destination);
        return true;
    }
}
