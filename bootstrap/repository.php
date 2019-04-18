<?php
declare(strict_types=1);

namespace Ixocreate\Package\Media;

use Ixocreate\Package\Media\Repository\MediaCreatedRepository;
use Ixocreate\Package\Media\Repository\MediaCropRepository;
use Ixocreate\Package\Media\Repository\MediaRepository;

/** @var \Ixocreate\Package\Database\Repository\RepositoryConfigurator $repository */
$repository->addRepository(MediaRepository::class);
$repository->addRepository(MediaCropRepository::class);
$repository->addRepository(MediaCreatedRepository::class);
