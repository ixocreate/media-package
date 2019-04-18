<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Package\Media\Config\Factory;

use Ixocreate\ServiceManager\FactoryInterface;
use Ixocreate\ServiceManager\ServiceManagerInterface;
use Ixocreate\Package\Media\Config\MediaConfig;
use Ixocreate\Package\Media\Config\MediaProjectConfig;
use Ixocreate\Package\ProjectUri\ProjectUri;

final class MediaConfigFactory implements FactoryInterface
{
    /**
     * @param ServiceManagerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @return MediaConfig|mixed
     */
    public function __invoke(ServiceManagerInterface $container, $requestedName, array $options = null)
    {
        return new MediaConfig($container->get(MediaProjectConfig::class), $container->get(ProjectUri::class));
    }
}
