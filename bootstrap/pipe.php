<?php
declare(strict_types=1);

namespace KiwiSuite\Media;

/** @var PipeConfigurator $pipe */
use KiwiSuite\Admin\Config\AdminConfig;
use KiwiSuite\ApplicationHttp\Pipe\GroupPipeConfigurator;
use KiwiSuite\ApplicationHttp\Pipe\PipeConfigurator;
use KiwiSuite\Media\Action\UploadAction;

$pipe->segmentPipe(AdminConfig::class)(function(PipeConfigurator $pipe) {
    $pipe->segment('/api')( function(PipeConfigurator $pipe) {

        $pipe->group("admin.authorized")(function (GroupPipeConfigurator $group) {
            $group->post('/media/upload', UploadAction::class, 'admin.api.media.upload');
        });
    });
});


