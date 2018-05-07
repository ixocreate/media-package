<?php
declare(strict_types=1);

namespace KiwiSuite\Media\Exceptions;

use Psr\Container\ContainerExceptionInterface;

class InvalidConfigException extends \RuntimeException implements
    ContainerExceptionInterface
{
}