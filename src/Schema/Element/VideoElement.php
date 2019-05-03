<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Schema\Element;

use Ixocreate\Media\Schema\Type\VideoType;
use Ixocreate\Schema\Element\AbstractSingleElement;

final class VideoElement extends AbstractSingleElement
{
    public function type(): string
    {
        return VideoType::class;
    }

    public function inputType(): string
    {
        return 'video';
    }

    public static function serviceName(): string
    {
        return 'video';
    }
}
