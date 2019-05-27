<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Test\Schema\Link;

use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\Link\MediaLink;
use Ixocreate\Media\Repository\MediaRepository;
use Ixocreate\Media\Uri\MediaUri;
use Ixocreate\Schema\Type\DateTimeType;
use Ixocreate\Schema\Type\UuidType;
use Ixocreate\Test\Schema\TypeMockHelper;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ixocreate\Schema\Link\MediaLink
 */
class MediaLinkTest extends TestCase
{
    private $mediaRepository;

    private $mediaUri;

    private $media;

    public function setUp()
    {
        (new TypeMockHelper($this, [
            UuidType::class => new UuidType(),
            UuidType::serviceName() => new UuidType(),
            DateTimeType::class => new DateTimeType(),
            DateTimeType::serviceName() => new DateTimeType(),
        ], false))->create();

        $this->media = new Media([
            'id' => 'b602adea-2a6a-4644-8426-d25b84aa8bca',
            'basePath' => '12/12/12/',
            'filename' => 'test.jpg',
            'mimeType' => 'image/jpeg',
            'size' => 12000,
            'publicStatus' => true,
            'hash' => 'b602adea-2a6a-4644-8426-d25b84aa8bca',
            'createdAt' => '2016-02-04 16:37:00',
            'updatedAt' => '2018-08-10 05:41:00',
        ]);
        $this->mediaRepository = $this->createMock(MediaRepository::class);
        $this->mediaRepository->method('find')->willReturnCallback(function ($id) {
            if ($id === 'b602adea-2a6a-4644-8426-d25b84aa8bca') {
                return $this->media;
            }

            return null;
        });

        $this->mediaUri = $this->createMock(MediaUri::class);
        $this->mediaUri->method('url')->willReturnCallback(function (Media $media) {
            return 'https://media.ixocreate.com/' . $media->basePath() . $media->filename();
        });
    }

    public function testServiceName()
    {
        $this->assertSame('media', MediaLink::serviceName());
    }

    public function testLabel()
    {
        $this->assertSame('Media', (new MediaLink($this->mediaRepository, $this->mediaUri))->label());
    }

    public function testCreate()
    {
        $mediaLink = new MediaLink($this->mediaRepository, $this->mediaUri);

        $newMediaLink = $mediaLink->create((string) $this->media->id());
        $this->assertNotSame($newMediaLink, $mediaLink);
        $this->assertSame((string) $this->media->id(), (string)$newMediaLink->toJson()['id']);

        $newMediaLink = $mediaLink->create(['id' => (string) $this->media->id()]);
        $this->assertNotSame($newMediaLink, $mediaLink);
        $this->assertSame((string) $this->media->id(), (string)$newMediaLink->toJson()['id']);

        $clonedMediaLink = $mediaLink->create($newMediaLink);
        $this->assertNotSame($clonedMediaLink, $newMediaLink);
        $this->assertSame((string) $this->media->id(), (string)$clonedMediaLink->toJson()['id']);

        $newMediaLink = $mediaLink->create('');
        $this->assertNotSame($newMediaLink, $mediaLink);
        $this->assertNull($newMediaLink->toJson());

        $newMediaLink = $mediaLink->create([]);
        $this->assertNotSame($newMediaLink, $mediaLink);
        $this->assertNull($newMediaLink->toJson());

        $newMediaLink = $mediaLink->create(['id' => 'dont_exist']);
        $this->assertNotSame($newMediaLink, $mediaLink);
        $this->assertNull($newMediaLink->toJson());

        $newMediaLink = $mediaLink->create('dont_exist');
        $this->assertNotSame($newMediaLink, $mediaLink);
        $this->assertNull($newMediaLink->toJson());
    }

    public function testToJson()
    {
        $mediaLink = new MediaLink($this->mediaRepository, $this->mediaUri);

        $newMediaLink = $mediaLink->create((string) $this->media->id());
        $this->assertSame($this->media->toPublicArray(), $newMediaLink->toJson());

        $newMediaLink = $mediaLink->create('doesnt_exist');
        $this->assertNull($newMediaLink->toJson());
    }

    public function testToDatabase()
    {
        $mediaLink = new MediaLink($this->mediaRepository, $this->mediaUri);

        $newMediaLink = $mediaLink->create((string) $this->media->id());
        $this->assertSame((string) $this->media->id(), $newMediaLink->toDatabase());

        $newMediaLink = $mediaLink->create('doesnt_exist');
        $this->assertNull($newMediaLink->toDatabase());
    }

    public function testAssemble()
    {
        $mediaLink = new MediaLink($this->mediaRepository, $this->mediaUri);

        $newMediaLink = $mediaLink->create((string) $this->media->id());
        $this->assertSame('https://media.ixocreate.com/' . $this->media->basePath() . $this->media->filename(), $newMediaLink->assemble());

        $newMediaLink = $mediaLink->create('doesnt_exist');
        $this->assertSame('', $newMediaLink->assemble());
    }

    public function testSerialize()
    {
        $mediaLink = new MediaLink($this->mediaRepository, $this->mediaUri);
        $mediaLink = $mediaLink->create((string) $this->media->id());
        $mediaLink = \unserialize(\serialize($mediaLink));

        $this->assertSame((string) $this->media->id(), $mediaLink->toDatabase());
    }
}
