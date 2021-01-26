<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Test\Media\Config\Factory;

use Ixocreate\Application\Service\ServiceManagerConfig;
use Ixocreate\Application\ServiceManager\ServiceManagerConfigurator;
use Ixocreate\Application\Uri\ApplicationUri;
use Ixocreate\Application\Uri\ApplicationUriConfigurator;
use Ixocreate\Media\Config\Factory\MediaConfigFactory;
use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\Config\MediaPackageConfig;
use Ixocreate\Media\MediaConfigurator;
use Ixocreate\ServiceManager\ServiceManager;
use Ixocreate\ServiceManager\ServiceManagerInterface;
use Ixocreate\ServiceManager\ServiceManagerSetup;
use PHPUnit\Framework\TestCase;

class MediaConfigFactoryTest extends TestCase
{
    /**
     * @var ServiceManagerInterface
     */
    private $serviceManager;

    public function setUp(): void
    {
        $serviceManagerConfigurator = new ServiceManagerConfigurator();

        $serviceManagerConfigurator->addService(MediaConfigurator::class);
        $serviceManagerConfigurator->addService(ApplicationUriConfigurator::class);
        $serviceManagerConfigurator->addFactory(MediaPackageConfig::class);
        $serviceManagerConfigurator->addFactory(ApplicationUri::class);

        $serviceManagerConfig = new ServiceManagerConfig($serviceManagerConfigurator);

        $serviceManagerSetup = new ServiceManagerSetup();

        $this->serviceManager = new ServiceManager($serviceManagerConfig, $serviceManagerSetup);
    }

    public function test__invoke()
    {
        $mediaConfigFactory = new MediaConfigFactory();

        $mediaConfig = $mediaConfigFactory($this->serviceManager, MediaConfig::class);

        $this->assertInstanceOf(MediaConfig::class, $mediaConfig);
    }
}
