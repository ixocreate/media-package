<?php
namespace KiwiSuite\Media;

use KiwiSuite\Contract\Application\ConfigProviderInterface;

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
}
