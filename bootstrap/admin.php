<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media;

use Ixocreate\Admin\AdminConfigurator;
use Ixocreate\Media\Admin\Config\Client\Provider\MediaProvider;

/** @var AdminConfigurator $admin */
$admin->addClientProvider(MediaProvider::class);
