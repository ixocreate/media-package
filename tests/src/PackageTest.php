<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Test\Media;

use Ixocreate\Media\Handler\MediaHandlerBootstrapItem;
use Ixocreate\Media\ImageDefinition\ImageDefinitionBootstrapItem;
use Ixocreate\Media\MediaBootstrapItem;
use Ixocreate\Media\Package;
use PHPUnit\Framework\TestCase;

class PackageTest extends TestCase
{
    /**
     * @covers \Ixocreate\Media\Package
     */
    public function testPackage()
    {
        $package = new Package();

        $this->assertSame([
            MediaBootstrapItem::class,
            MediaHandlerBootstrapItem::class,
            ImageDefinitionBootstrapItem::class,
        ], $package->getBootstrapItems());

        $this->assertDirectoryExists($package->getBootstrapDirectory());
        $this->assertEmpty($package->getDependencies());
    }
}
