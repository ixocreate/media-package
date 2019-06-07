<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

use Ixocreate\Application\Http\Middleware\MiddlewareConfigurator;

/** @var MiddlewareConfigurator $middleware */
$middleware->addAction(\Ixocreate\Media\Action\StreamAction::class);
$middleware->addAction(\Ixocreate\Media\Action\UploadAction::class);
$middleware->addAction(\Ixocreate\Media\Action\Image\EditorAction::class);
$middleware->addAction(\Ixocreate\Media\Action\Media\DetailAction::class);
$middleware->addAction(\Ixocreate\Media\Action\Media\UpdateAction::class);
$middleware->addAction(\Ixocreate\Media\Action\Media\DeleteAction::class);
$middleware->addAction(\Ixocreate\Media\Action\Media\IndexAction::class);
