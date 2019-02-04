<?php
/**
 * @see https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media;

use Ixocreate\Contract\Application\ConfigProviderInterface;

final class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @return array
     */
    public function __invoke(): array
    {
        return [
            'media' => [
                'driver' => 'automatic',
                'uri' => '/media',
            ],
        ];
    }

    public function configName(): string
    {
        return 'media';
    }

    public function configContent(): string
    {
        return \file_get_contents(__DIR__ . '/../resources/media.config.example.php');
    }
}
