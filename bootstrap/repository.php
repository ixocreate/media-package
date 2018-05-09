<?php
declare(strict_types=1);

namespace KiwiSuite\Media;

use KiwiSuite\Media\Repository\MediaRepository;

/** @var \KiwiSuite\Database\Repository\RepositoryConfigurator $repository */
$repository->addRepository(MediaRepository::class);