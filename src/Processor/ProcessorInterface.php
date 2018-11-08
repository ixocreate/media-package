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
/**
 * Created by PhpStorm.
 * User: afriedrich
 * Date: 20.08.18
 * Time: 07:54
 */
namespace KiwiSuite\Media\Processor;

use KiwiSuite\Contract\ServiceManager\NamedServiceInterface;

interface ProcessorInterface extends NamedServiceInterface
{
    public function process();
}
