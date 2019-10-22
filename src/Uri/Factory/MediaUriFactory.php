<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Uri\Factory;

use Ixocreate\Admin\Config\AdminConfig;
use Ixocreate\Application\Uri\ApplicationUri;
use Ixocreate\Cache\CacheableSubManager;
use Ixocreate\Cache\CacheManager;
use Ixocreate\Media\Cacheable\UrlVariantCacheable;
use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\Config\MediaPaths;
use Ixocreate\Media\Handler\MediaHandlerSubManager;
use Ixocreate\Media\Uri\MediaUri;
use Ixocreate\ServiceManager\FactoryInterface;
use Ixocreate\ServiceManager\ServiceManagerInterface;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;

final class MediaUriFactory implements FactoryInterface
{
    /**
     * @param ServiceManagerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @return MediaUri|mixed
     */
    public function __invoke(ServiceManagerInterface $container, $requestedName, array $options = null)
    {
        $packages = new Packages();

        $adminConfig = $container->get(AdminConfig::class);

        $urlPackage = new UrlPackage(
            (string)$container->get(MediaConfig::class)->uri(),
            new EmptyVersionStrategy()
        );
        $packages->setDefaultPackage($urlPackage);

        $urlPackage = new UrlPackage(
            (string)$container->get(ApplicationUri::class)->getMainUrl() . '/' . MediaPaths::STREAM_PATH,
            new EmptyVersionStrategy()
        );
        $packages->addPackage('streamMedia', $urlPackage);

        $delegatorSubManager = $container->get(MediaHandlerSubManager::class);

        return new MediaUri(
            $packages,
            $adminConfig,
            $delegatorSubManager,
            $container->get(CacheManager::class),
            $container->get(CacheableSubManager::class)->get(UrlVariantCacheable::class)
        );
    }
}
