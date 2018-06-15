<?php
/**
 * kiwi-suite/media (https://github.com/kiwi-suite/media)
 *
 * @package kiwi-suite/media
 * @see https://github.com/kiwi-suite/media
 * @copyright Copyright (c) 2010 - 2018 kiwi suite GmbH
 * @license MIT License
 */
declare (strict_types=1);

namespace KiwiSuite\Media\Delegator;

use KiwiSuite\Contract\ServiceManager\NamedServiceInterface;
use KiwiSuite\Media\Entity\Media;

interface DelegatorInterface extends NamedServiceInterface
{
    public function responsible(Media $media);
}