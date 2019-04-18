<?php
namespace Ixocreate\Media\Package;

/** @var \Ixocreate\Admin\Package\Config\AdminConfigurator $admin */

use Ixocreate\Media\Package\Admin\Config\Client\Provider\MediaProvider;

$admin->addClientProvider(MediaProvider::class);

