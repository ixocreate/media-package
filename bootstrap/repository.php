<?php
declare(strict_types=1);

namespace Ixocreate\Media;

use Ixocreate\Media\Repository\MediaCreatedRepository;
use Ixocreate\Media\Repository\MediaCropRepository;
use Ixocreate\Media\Repository\MediaRepository;

/** @var \Ixocreate\Database\Repository\RepositoryConfigurator $repository */
$repository->addRepository(MediaRepository::class);
$repository->addRepository(MediaCropRepository::class);
$repository->addRepository(MediaCreatedRepository::class);
