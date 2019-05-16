<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Test\Media\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Ixocreate\Media\Entity\MediaCreated;
use Ixocreate\Media\Repository\MediaCreatedRepository;
use PHPUnit\Framework\TestCase;

class MediaCreatedRepositoryTest extends TestCase
{

    public function testGetEntityName()
    {
        $master = $this->createMock(EntityManagerInterface::class);
        $repository = new MediaCreatedRepository($master);
        $this->assertSame(MediaCreated::class, $repository->getEntityName());
    }
}
