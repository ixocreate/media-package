<?php
/**
 * @see https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Exception;

use Psr\Container\ContainerExceptionInterface;

class FileTypeNotSupportedException extends \RuntimeException implements ContainerExceptionInterface
{
}
