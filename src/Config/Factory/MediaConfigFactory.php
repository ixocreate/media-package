<?php
/**
 * kiwi-suite/media (https://github.com/kiwi-suite/media)
 *
 * @package kiwi-suite/media
 * @see https://github.com/kiwi-suite/media
 * @copyright Copyright (c) 2010 - 2018 kiwi suite GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace KiwiSuite\Media\Config\Factory;

use KiwiSuite\Config\Config;
use KiwiSuite\Contract\ServiceManager\FactoryInterface;
use KiwiSuite\Contract\ServiceManager\ServiceManagerInterface;
use KiwiSuite\Media\Config\MediaConfig;
use KiwiSuite\Media\Config\MediaProjectConfig;
use Zend\Diactoros\Uri;
use KiwiSuite\ProjectUri\ProjectUri;

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
