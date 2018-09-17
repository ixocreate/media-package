<?php
declare(strict_types=1);

namespace KiwiSuite\Media;

/** @var PipeConfigurator $pipe */
use KiwiSuite\Admin\Config\AdminConfig;
use KiwiSuite\ApplicationHttp\Pipe\GroupPipeConfigurator;
use KiwiSuite\ApplicationHttp\Pipe\PipeConfigurator;
use KiwiSuite\Media\Action\Image\ImageDefinitionDetailAction;
use KiwiSuite\Media\Action\Image\ImageDefinitionListAction;
use KiwiSuite\Media\Action\Media\ChangePublicStatusAction;
use KiwiSuite\Media\Action\Media\DeleteAction;
use KiwiSuite\Media\Action\Media\FilterAction;
use KiwiSuite\Media\Action\UploadAction;

$pipe->segmentPipe(AdminConfig::class)(function(PipeConfigurator $pipe) {
    $pipe->segment('/api')( function(PipeConfigurator $pipe) {
        $pipe->get('/media/imageDefinition/list', ImageDefinitionListAction::class, 'admin.api.media.imageDefinition.list');

        $pipe->group("admin.authorized")(function (GroupPipeConfigurator $group) {
            $group->post('/media/upload', UploadAction::class, 'admin.api.media.upload');
            $group->post('/media/filter', FilterAction::class, 'admin.api.media.filter');
            $group->patch('/media/public/{id}', ChangePublicStatusAction::class, 'admin.api.media.publicStatus');
            $group->delete('/media/{id}', DeleteAction::class, 'admin.api.media.delete');
        });
    });
});


