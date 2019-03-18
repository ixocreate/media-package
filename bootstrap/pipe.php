<?php
declare(strict_types=1);

namespace Ixocreate\Media;

/** @var PipeConfigurator $pipe */

use Ixocreate\Admin\Config\AdminConfig;
use Ixocreate\ApplicationHttp\Pipe\GroupPipeConfigurator;
use Ixocreate\ApplicationHttp\Pipe\PipeConfigurator;
use Ixocreate\Media\Action\Image\EditorAction;
use Ixocreate\Media\Action\Image\ImageDefinitionDetailAction;
use Ixocreate\Media\Action\Image\ImageDefinitionListAction;
use Ixocreate\Media\Action\Media\ChangePublicStatusAction;
use Ixocreate\Media\Action\Media\DeleteAction;
use Ixocreate\Media\Action\Media\DetailAction;
use Ixocreate\Media\Action\Media\EditAction;
use Ixocreate\Media\Action\Media\FilterAction;
use Ixocreate\Media\Action\Media\IndexAction;
use Ixocreate\Media\Action\Media\PrivateStreamAction;
use Ixocreate\Media\Action\Media\UpdateAction;
use Ixocreate\Media\Action\StreamAction;
use Ixocreate\Media\Action\UploadAction;
use Ixocreate\Media\Middleware\StreamMiddleware;

$pipe->get('/media/stream/{token}', StreamAction::class, 'media.stream');

$pipe->segmentPipe(AdminConfig::class)(function (PipeConfigurator $pipe) {
    $pipe->segment('/api')(function (PipeConfigurator $pipe) {
        $pipe->group("admin.authorized")(function (GroupPipeConfigurator $group) {
            $group->get('/media', IndexAction::class, 'admin.api.media.index');
            $group->get('/media/{id}', DetailAction::class, 'admin.api.media.detail');
            $group->patch('/media/{id}', UpdateAction::class, 'admin.api.media.update');
            $group->post('/media/editor', EditorAction::class, 'admin.api.media.editor');
            $group->post('/media/upload', UploadAction::class, 'admin.api.media.upload');
            $group->delete('/media/{id}', DeleteAction::class, 'admin.api.media.delete');
        });
    });
});


