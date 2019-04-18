<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Package\Media\Exception;

use Psr\Container\ContainerExceptionInterface;

class FileDuplicateException extends \RuntimeException implements ContainerExceptionInterface
{
}
