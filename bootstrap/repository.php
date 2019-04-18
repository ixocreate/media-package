<?php
declare(strict_types=1);

namespace Ixocreate\Media\Package;

use Ixocreate\Media\Package\Repository\MediaCreatedRepository;
use Ixocreate\Media\Package\Repository\MediaCropRepository;
use Ixocreate\Media\Package\Repository\MediaRepository;

/** @var \Ixocreate\Database\Package\Repository\RepositoryConfigurator $repository */
$repository->addRepository(MediaRepository::class);
$repository->addRepository(MediaCropRepository::class);
$repository->addRepository(MediaCreatedRepository::class);
