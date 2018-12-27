<?php
/**
 * kiwi-suite/media (https://github.com/kiwi-suite/media)
 *
 * @package kiwi-suite/media
 * @see https://github.com/kiwi-suite/media
 * @copyright Copyright (c) 2010 - 2018 kiwi suite GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Delegator;

use Ixocreate\Contract\ServiceManager\NamedServiceInterface;
use Ixocreate\Media\Entity\Media;

interface DelegatorInterface extends NamedServiceInterface
{
    /**
     * @param Media $media
     * @return bool
     */
    public function isResponsible(Media $media): bool;

    /**
     * @return array
     */
    public function directories(): array;

    /**
     * @param Media $media
     */
    public function process(Media $media): void;
}
