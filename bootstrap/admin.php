<?php

namespace Ixocreate\Media;

use Ixocreate\Admin\AdminConfigurator;
use Ixocreate\Media\Admin\Config\Client\Provider\MediaProvider;

/** @var AdminConfigurator $admin */

$admin->addClientProvider(MediaProvider::class);

