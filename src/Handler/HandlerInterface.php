<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Handler;

use Ixocreate\Media\MediaInterface;
use Ixocreate\ServiceManager\NamedServiceInterface;

interface HandlerInterface extends NamedServiceInterface
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
     */
    public function process(MediaInterface $media): void;
}
