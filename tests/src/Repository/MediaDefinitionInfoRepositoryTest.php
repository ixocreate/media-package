<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Test\Media\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Ixocreate\Media\Entity\MediaDefinitionInfo;
use Ixocreate\Media\Repository\MediaDefinitionInfoRepository;
use PHPUnit\Framework\TestCase;

class MediaDefinitionInfoRepositoryTest extends TestCase
{
    public function testGetEntityName()
    {
        $master = $this->createMock(EntityManagerInterface::class);
        $repository = new MediaDefinitionInfoRepository($master);
        $this->assertSame(MediaDefinitionInfo::class, $repository->getEntityName());
    }
}
