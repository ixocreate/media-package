<?php

declare(strict_types=1);

namespace KiwiSuite\Media\MediaCreateHandler;

class TempFileHandler implements MediaCreateHandlerInterface
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

    public function move($destination): bool
    {
        return rename($this->file, $destination);
    }
}
