<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Config\Factory;

use Ixocreate\Application\Uri\ApplicationUri;
use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\Config\MediaProjectConfig;
use Ixocreate\ServiceManager\FactoryInterface;
use Ixocreate\ServiceManager\ServiceManagerInterface;

final class MediaConfigFactory implements FactoryInterface
{
    /**
     * @param ServiceManagerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return MediaConfig|mixed
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ServiceManagerInterface $container, $requestedName, array $options = null)
    {
        return new MediaConfig($container->get(MediaProjectConfig::class), $container->get(ApplicationUri::class));
    }
}
