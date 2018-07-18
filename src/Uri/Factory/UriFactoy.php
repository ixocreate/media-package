<?php
namespace KiwiSuite\Media\Uri\Factory;

use KiwiSuite\Contract\ServiceManager\FactoryInterface;
use KiwiSuite\Contract\ServiceManager\ServiceManagerInterface;
use KiwiSuite\Media\Config\MediaConfig;
use KiwiSuite\Media\Uri\Uri;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;

final class UriFactoy implements FactoryInterface
{

    /**
     * @param ServiceManagerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return mixed
     */
    public function __invoke(ServiceManagerInterface $container, $requestedName, array $options = null)
    {
        $packages = new Packages();

        $urlPackage = new UrlPackage(
            (string) $container->get(MediaConfig::class)->getUri(),
            new EmptyVersionStrategy()
        );
        $packages->setDefaultPackage($urlPackage);

        return new Uri($packages);
    }
}
