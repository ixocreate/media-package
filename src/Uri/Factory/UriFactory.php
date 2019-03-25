<?php
/**
 * @see https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Uri\Factory;

use Ixocreate\Admin\Config\AdminConfig;
use Ixocreate\Contract\ServiceManager\FactoryInterface;
use Ixocreate\Contract\ServiceManager\ServiceManagerInterface;
use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\Delegator\DelegatorSubManager;
use Ixocreate\Media\MediaPaths;
use Ixocreate\Media\Uri\Uri;
use Ixocreate\ProjectUri\ProjectUri;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;

final class UriFactory implements FactoryInterface
{
    /**
     * @param ServiceManagerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @return Uri|mixed
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
            (string)$container->get(ProjectUri::class)->getMainUrl() . '/' . MediaPaths::STREAM_PATH,
            new EmptyVersionStrategy()
        );
        $packages->addPackage('streamMedia', $urlPackage);

        $delegatorSubManager = $container->get(DelegatorSubManager::class);

        return new Uri($packages, $adminConfig, $delegatorSubManager);
    }
}
