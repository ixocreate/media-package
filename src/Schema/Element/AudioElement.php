<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Schema\Element;

use Ixocreate\Media\Schema\Type\AudioType;
use Ixocreate\Schema\Element\AbstractSingleElement;

final class AudioElement extends AbstractSingleElement
{
    public function type(): string
    {
        return AudioType::class;
    }

    public function inputType(): string
    {
        return 'audio';
    }

    public static function serviceName(): string
    {
        return 'audio';
    }
}
