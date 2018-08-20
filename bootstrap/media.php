<?php
declare(strict_types=1);

namespace KiwiSuite\Media;

use KiwiSuite\Media\Config\MediaConfigurator;

/** @var MediaConfigurator $media */
$media->setImageWhiteliste([
   'jpg' => 'image/jpeg',
   'jpeg' => 'image/jpeg',
   'gif' => 'image/gif',
   'png' => 'image/png' 
]);
$media->setTextWhitelist([
   'txt' =>  'text/plain'
]);
$media->setPublicStatus(false);