<?php
/**
 * @see https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\MediaCreateHandler;

interface MediaCreateHandlerInterface
{
    public function filename(): string;

    public function tempFile(): string;

    public function move($destination): bool;
}
