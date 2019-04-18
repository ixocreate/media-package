<?php
namespace Ixocreate\Package\Media;

/** @var \Ixocreate\Package\Admin\Config\AdminConfigurator $admin */

use Ixocreate\Package\Media\Admin\Config\Client\Provider\MediaProvider;

$admin->addClientProvider(MediaProvider::class);

