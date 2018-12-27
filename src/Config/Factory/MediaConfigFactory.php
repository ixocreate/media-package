<?php
/**
 * @see https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Config\Factory;

use Ixocreate\Config\Config;
use Ixocreate\Contract\ServiceManager\FactoryInterface;
use Ixocreate\Contract\ServiceManager\ServiceManagerInterface;
use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\Config\MediaProjectConfig;
use Zend\Diactoros\Uri;
use Ixocreate\ProjectUri\ProjectUri;

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
        $config = $container->get(Config::class);
        $driver = $config->get('media.driver');

        $uri = new Uri($config->get('media.uri'));
        if (empty($uri->getHost())) {
            /** @var ProjectUri $projectUri */
            $projectUri = $container->get(ProjectUri::class);

            $uri = $uri->withPath(\rtrim($projectUri->getMainUrl()->getPath(), '/') . '/' . \ltrim($uri->getPath(), '/'));
            $uri = $uri->withHost($projectUri->getMainUrl()->getHost());
            $uri = $uri->withScheme($projectUri->getMainUrl()->getScheme());
            $uri = $uri->withPort($projectUri->getMainUrl()->getPort());
        }

        return new MediaConfig($driver, $uri, $container->get(MediaProjectConfig::class));
    }
}
