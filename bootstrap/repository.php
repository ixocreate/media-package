<?php
declare(strict_types=1);

namespace KiwiSuite\Media;

use KiwiSuite\Media\Repository\MediaCreatedRepository;
use KiwiSuite\Media\Repository\MediaCropRepository;
use KiwiSuite\Media\Repository\MediaRepository;

/** @var \KiwiSuite\Database\Repository\RepositoryConfigurator $repository */
$repository->addRepository(MediaRepository::class);
$repository->addRepository(MediaCropRepository::class);
$repository->addRepository(MediaCreatedRepository::class);