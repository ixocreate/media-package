<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media;

use Ixocreate\Database\Repository\RepositoryConfigurator;
use Ixocreate\Media\Repository\MediaDefinitionInfoRepository;
use Ixocreate\Media\Repository\MediaRepository;

/** @var RepositoryConfigurator $repository */
$repository->addRepository(MediaRepository::class);
$repository->addRepository(MediaDefinitionInfoRepository::class);
