<?php

declare(strict_types=1);

namespace KiwiSuite\Media\MediaCreateHandler;

interface MediaCreateHandlerInterface
{
    public function filename(): string;
    public function tempFile(): string;
    public function move($destination): bool;
}
