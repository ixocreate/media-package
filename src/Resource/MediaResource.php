<?php
/**
 * kiwi-suite/admin (https://github.com/kiwi-suite/media)
 *
 * @package   kiwi-suite/media
 * @see       https://github.com/kiwi-suite/media
 * @copyright Copyright (c) 2010 - 2018 kiwi suite GmbH
 * @license   MIT License
 */

declare(strict_types=1);

namespace KiwiSuite\Media\Resource;

use KiwiSuite\Admin\Resource\ResourceInterface;
use KiwiSuite\Admin\Resource\ResourceTrait;
use KiwiSuite\Media\Repository\MediaRepository;

final class MediaResource implements ResourceInterface
{
    use ResourceTrait;

    public static function name(): string
    {
        return "media";
    }

    public function repository(): string
    {
        return MediaRepository::class;
    }

    public function icon(): string
    {
        return "fa";
    }

    public function schema(): array
    {
        return [
            'name'       => 'Media',
            'namePlural' => 'Media',
        ];
    }
}
