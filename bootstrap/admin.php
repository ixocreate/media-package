<?php
namespace Ixocreate\Media;

/** @var \Ixocreate\Admin\Config\AdminConfigurator $admin */

use Ixocreate\Media\Admin\Config\Client\Provider\MediaProvider;

$admin->addClientProvider(MediaProvider::class);

