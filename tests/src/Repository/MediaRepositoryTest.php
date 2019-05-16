<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Test\Media\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\Repository\MediaRepository;
use PHPUnit\Framework\TestCase;

class MediaRepositoryTest extends TestCase
{

    public function testGetEntityName()
    {
        $master = $this->createMock(EntityManagerInterface::class);
        $repository = new MediaRepository($master);
        $this->assertSame(Media::class, $repository->getEntityName());

    }
}
