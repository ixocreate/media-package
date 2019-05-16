<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Test\Media\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Ixocreate\Media\Entity\MediaCrop;
use Ixocreate\Media\Repository\MediaCropRepository;
use PHPUnit\Framework\TestCase;

class MediaCropRepositoryTest extends TestCase
{

    public function testGetEntityName()
    {
        $master = $this->createMock(EntityManagerInterface::class);
        $repository = new MediaCropRepository($master);
        $this->assertSame(MediaCrop::class, $repository->getEntityName());
    }
}
