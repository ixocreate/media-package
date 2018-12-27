<?php
/**
 * @see https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\MediaCreateHandler;

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
        \rename($this->file, $destination);
        \chmod($destination, 0655);
        return true;
    }
}
