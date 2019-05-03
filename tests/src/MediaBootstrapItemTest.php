<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Test\Media;

use Ixocreate\Application\Configurator\ConfiguratorInterface;
use Ixocreate\Media\MediaBootstrapItem;
use PHPUnit\Framework\TestCase;

class MediaBootstrapItemTest extends TestCase
{
    /**
     * @var MediaBootstrapItem
     */
    private $mediaBootstrapItem;

    public function setUp()
    {
        $this->mediaBootstrapItem = new MediaBootstrapItem();
    }

    /**
     * @covers \Ixocreate\Media\MediaBootstrapItem
     */
    public function testMediaBootstrapItem()
    {
        $this->assertSame($this->mediaBootstrapItem->getVariableName(), 'media');
        $this->assertSame($this->mediaBootstrapItem->getFileName(), 'media.php');
        $this->assertInstanceOf(ConfiguratorInterface::class, $this->mediaBootstrapItem->getConfigurator());
    }
}