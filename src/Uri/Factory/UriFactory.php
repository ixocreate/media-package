<?php
declare(strict_types=1);

namespace KiwiSuite\Media\Uri\Factory;

use KiwiSuite\Contract\ServiceManager\FactoryInterface;
use KiwiSuite\Contract\ServiceManager\ServiceManagerInterface;
use KiwiSuite\Media\Config\MediaConfig;
use KiwiSuite\Media\Uri\Uri;
use KiwiSuite\ProjectUri\ProjectUri;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;

final class UriFactory implements FactoryInterface
{

    /**
     * @param ServiceManagerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Uri|mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ServiceManagerInterface $container, $requestedName, array $options = null)
    {
        $packages = new Packages();

        $urlPackage = new UrlPackage(
            (string) $container->get(MediaConfig::class)->uri(),
            new EmptyVersionStrategy()
        );
        $packages->setDefaultPackage($urlPackage);

        $urlPackage = new UrlPackage(
            (string) $container->get(ProjectUri::class)->getMainUrl() . '/media/stream/',
            new EmptyVersionStrategy()
        );
        $packages->addPackage('streamMedia', $urlPackage);

        return new Uri($packages);
    }
}
