<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Handler;

use Ixocreate\Application\ServiceManager\NamedServiceInterface;
use Ixocreate\Filesystem\FilesystemInterface;
use Ixocreate\Media\MediaInterface;

interface MediaHandlerInterface extends NamedServiceInterface
{
    /**
     * @param MediaInterface $media
     * @return bool
     */
    public function isResponsible(MediaInterface $media): bool;

    /**
     * @return array
     */
    public function directories(): array;

    /**
     * @param MediaInterface $media
     * @param FilesystemInterface $filesystem
     */
    public function process(MediaInterface $media, FilesystemInterface $filesystem): void;
}
