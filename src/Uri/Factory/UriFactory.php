<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Package\Uri\Factory;

use Ixocreate\Admin\Package\Config\AdminConfig;
use Ixocreate\ServiceManager\FactoryInterface;
use Ixocreate\ServiceManager\ServiceManagerInterface;
use Ixocreate\Media\Package\Config\MediaConfig;
use Ixocreate\Media\Package\Delegator\DelegatorSubManager;
use Ixocreate\Media\Package\MediaPaths;
use Ixocreate\Media\Package\Uri\Uri;
use Ixocreate\Application\Uri\ApplicationUri;
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
            (string)$container->get(Uri::class)->getMainUrl() . '/' . MediaPaths::STREAM_PATH,
            new EmptyVersionStrategy()
        );
        $packages->addPackage('streamMedia', $urlPackage);

        $delegatorSubManager = $container->get(DelegatorSubManager::class);

        return new Uri($packages, $adminConfig, $delegatorSubManager);
    }
}
