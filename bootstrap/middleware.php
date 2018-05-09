<?php
declare(strict_types=1);

namespace KiwiSuite\Media;

use KiwiSuite\ApplicationHttp\Middleware\MiddlewareConfigurator;
use KiwiSuite\Media\UploadAction;

/** @var MiddlewareConfigurator $middleware */
$middleware->addAction(UploadAction::class);