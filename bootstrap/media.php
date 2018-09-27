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
$media->setVideoWhitelist([
    'mpg' => 'video/mpeg',
    'mp4' => 'video/mp4',
    'quicktime' => 'video/quicktime',
    'lsf' => 'video/x-la-asf',
    'asf' => 'video/x-ms-asf',
    'avi' => 'video/x-msvideo',
    'wav' => 'video/x-wav',
]);
$media->setAudioWhitelist([
    'snd' => 'audio/basic',
    'mid' => 'audio/mid',
    'mpeg' => 'audio/mp3',
]);
$media->setDocumentWhitelist([
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'doc' => 'application/msword',
    'dot' => 'application/msword',
    'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
    'docm' => 'application/vnd.ms-word.document.macroEnabled.12',
    'dotm' => 'application/vnd.ms-word.template.macroEnabled.12',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'xls' => 'application/vnd.ms-excel',
    'xlt' => 'application/vnd.ms-excel',
    'xla' => 'application/vnd.ms-excel',
    'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
    'xlsm' => 'application/vnd.ms-excel.sheet.macroEnabled.12',
    'xltm' => 'application/vnd.ms-excel.template.macroEnabled.12',
    'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
    'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
    'ppt' => 'application/vnd.ms-powerpoint',
    'pot' => 'application/vnd.ms-powerpoint',
    'pps' => 'application/vnd.ms-powerpoint',
    'ppa' => 'application/vnd.ms-powerpoint',
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
    'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
    'ppam' => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
    'pptm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
    'potm' => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
    'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
    'pdf' => 'application/pdf',
]);
$media->setPublicStatus(false);