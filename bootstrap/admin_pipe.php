<?php
namespace KiwiSuite\Admin;
/** @var \KiwiSuite\Admin\Pipe\PipeConfigurator $pipe */

use KiwiSuite\Admin\Middleware\Api\AuthorizationGuardMiddleware;
use KiwiSuite\ApplicationHttp\Pipe\GroupPipeConfigurator;
use KiwiSuite\ApplicationHttp\Pipe\PipeConfigurator;
use KiwiSuite\Media\Action\IndexAction;
use KiwiSuite\Media\Action\UploadAction;

$pipe->segment('/api', function(PipeConfigurator $pipe) {
    //Authorized routes
    $pipe->group(function (GroupPipeConfigurator $group) {
        $group->before(AuthorizationGuardMiddleware::class);

        $group->post('/media/upload', UploadAction::class, 'admin.api.media.upload');
        $group->get('/media', IndexAction::class, 'admin.api.media.index');
    });
});
