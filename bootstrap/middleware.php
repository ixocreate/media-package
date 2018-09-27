<?php
declare(strict_types=1);

use KiwiSuite\ApplicationHttp\Middleware\MiddlewareConfigurator;

/** @var MiddlewareConfigurator $middleware */
$middleware->addAction(\KiwiSuite\Media\Action\IndexAction::class);
$middleware->addAction(\KiwiSuite\Media\Action\Image\EditorAction::class);
$middleware->addAction(\KiwiSuite\Media\Action\Media\DetailAction::class);
$middleware->addAction(\KiwiSuite\Media\Action\UploadAction::class);
$middleware->addAction(\KiwiSuite\Media\Action\Media\ChangePublicStatusAction::class);
$middleware->addAction(\KiwiSuite\Media\Action\Media\DeleteAction::class);
$middleware->addAction(\KiwiSuite\Media\Action\Media\FilterAction::class);
$middleware->addAction(\KiwiSuite\Media\Action\Media\IndexAction::class);