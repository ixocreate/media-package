<?php
declare(strict_types=1);

use KiwiSuite\ApplicationHttp\Middleware\MiddlewareConfigurator;

/** @var MiddlewareConfigurator $middleware */
$middleware->addAction(\KiwiSuite\Media\Action\UploadAction::class);
$middleware->addAction(\KiwiSuite\Media\Action\IndexAction::class);
$middleware->addAction(\KiwiSuite\Media\Action\EditorAction::class);
$middleware->addAction(\KiwiSuite\Media\Action\Media\DetailAction::class);
$middleware->addAction(\KiwiSuite\Media\Action\Media\UpdateAction::class);