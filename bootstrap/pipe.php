<?php
declare(strict_types=1);

namespace Ixocreate\Package\Media;

/** @var PipeConfigurator $pipe */

use Ixocreate\Package\Admin\Config\AdminConfig;
use Ixocreate\Application\Http\Pipe\GroupPipeConfigurator;
use Ixocreate\Application\Http\Pipe\PipeConfigurator;
use Ixocreate\Package\Media\Action\Image\EditorAction;
use Ixocreate\Package\Media\Action\Media\DeleteAction;
use Ixocreate\Package\Media\Action\Media\DetailAction;
use Ixocreate\Package\Media\Action\Media\IndexAction;
use Ixocreate\Package\Media\Action\Media\UpdateAction;
use Ixocreate\Package\Media\Action\StreamAction;
use Ixocreate\Package\Media\Action\UploadAction;

$pipe->get('/media/stream/{token}', StreamAction::class, 'media.stream');

/**
 * TODO: this should only be registered if admin is even included in the setup
 */
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


