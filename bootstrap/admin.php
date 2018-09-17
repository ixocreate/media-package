<?php
namespace KiwiSuite\Media;

/** @var \KiwiSuite\Admin\Config\AdminConfigurator $admin */

use KiwiSuite\Media\Admin\Config\Client\Provider\MediaProvider;

$admin->addClientProvider(MediaProvider::class);

