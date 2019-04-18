<?php
declare(strict_types=1);

use Ixocreate\Application\Http\Middleware\MiddlewareConfigurator;

/** @var MiddlewareConfigurator $middleware */
$middleware->addAction(\Ixocreate\Media\Package\Action\Image\EditorAction::class);
$middleware->addAction(\Ixocreate\Media\Package\Action\Media\DetailAction::class);
$middleware->addAction(\Ixocreate\Media\Package\Action\StreamAction::class);
$middleware->addAction(\Ixocreate\Media\Package\Action\UploadAction::class);
$middleware->addAction(\Ixocreate\Media\Package\Action\Media\UpdateAction::class);
$middleware->addAction(\Ixocreate\Media\Package\Action\Media\DeleteAction::class);
$middleware->addAction(\Ixocreate\Media\Package\Action\Media\IndexAction::class);
