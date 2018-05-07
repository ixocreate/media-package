<?php
declare(strict_types=1);

namespace KiwiSuite\Media\Exceptions;

use Psr\Container\ContainerExceptionInterface;

class InvalidExtensionException extends \RuntimeException implements
    ContainerExceptionInterface
{
}