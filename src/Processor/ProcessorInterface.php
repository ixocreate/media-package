<?php
/**
 * @see https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: afriedrich
 * Date: 20.08.18
 * Time: 07:54
 */

namespace Ixocreate\Media\Processor;

use Ixocreate\Contract\ServiceManager\NamedServiceInterface;

interface ProcessorInterface extends NamedServiceInterface
{
    public function process();
}
