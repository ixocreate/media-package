<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Action;

use Ixocreate\Admin\Entity\User;
use Ixocreate\Admin\Response\ApiErrorResponse;
use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\CommandBus\CommandBus;
use Ixocreate\Filesystem\FilesystemManager;
use Ixocreate\Media\Command\Media\CreateCommand;
use Ixocreate\Media\CreateHandler\UploadHandler;
use Ixocreate\Media\Exception\InvalidConfigException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\UploadedFile;

final class UploadAction implements MiddlewareInterface
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var FilesystemManager
     */
    private $filesystemManager;

    /**
     * UploadAction constructor.
     *
     * @param CommandBus $commandBus
     * @param FilesystemManager $filesystemManager
     */
    public function __construct(CommandBus $commandBus, FilesystemManager $filesystemManager)
    {
        $this->commandBus = $commandBus;
        $this->filesystemManager = $filesystemManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @throws \Exception
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!\array_key_exists('file', $request->getUploadedFiles())) {
            return new ApiErrorResponse('invalid_file');
        }

        $upload = $request->getUploadedFiles()['file'];

        if (!($upload instanceof UploadedFile)) {
            return new ApiErrorResponse('invalid_file');
        }

        /** @var CreateCommand $createCommand */
        $createCommand = $this->commandBus->create(CreateCommand::class, []);

        $handler = new UploadHandler($upload);
        $createCommand = $createCommand->withMediaCreateHandler($handler);

        $user = $request->getAttribute(User::class);
        $createCommand = $createCommand->withCreatedUser($user);

        if (!$this->filesystemManager->has('media')) {
            throw new InvalidConfigException('Filesystem Config not set');
        }

        $filesystem = $this->filesystemManager->get('media');

        $createCommand = $createCommand->withFilesystem($filesystem);

        $commandResult = $this->commandBus->dispatch($createCommand);

        if (!$commandResult->isSuccessful()) {
            return new ApiErrorResponse('media_create_media', $commandResult->messages());
        }

        return new ApiSuccessResponse(['id' => $createCommand->uuid()]);
    }
}
