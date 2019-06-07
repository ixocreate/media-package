<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Action\Media;

use Ixocreate\Admin\Response\ApiErrorResponse;
use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\CommandBus\CommandBus;
use Ixocreate\Filesystem\FilesystemManager;
use Ixocreate\Media\Command\Media\DeleteCommand;
use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\Exception\InvalidConfigException;
use Ixocreate\Media\Repository\MediaRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class DeleteAction implements MiddlewareInterface
{
    /**
     * @var MediaRepository
     */
    private $mediaRepository;

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var FilesystemManager
     */
    private $filesystemManager;

    /**
     * DeleteAction constructor.
     *
     * @param MediaRepository $mediaRepository
     * @param CommandBus $commandBus
     * @param FilesystemManager $filesystemManager
     */
    public function __construct(
        MediaRepository $mediaRepository,
        CommandBus $commandBus,
        FilesystemManager $filesystemManager
    ) {
        $this->mediaRepository = $mediaRepository;
        $this->commandBus = $commandBus;
        $this->filesystemManager = $filesystemManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Media $media */
        $media = $this->mediaRepository->findOneBy(['id' => $request->getAttribute('id')]);

        if (empty($media)) {
            return new ApiErrorResponse('given media Id does not exist');
        }

        if (!$this->filesystemManager->has('media')) {
            throw new InvalidConfigException('Filesystem Config not set');
        }

        $filesystem = $this->filesystemManager->get('media');

        /** @var DeleteCommand $deleteCommand */
        $deleteCommand = $this->commandBus->create(DeleteCommand::class, ['media' => $media]);

        $deleteCommand = $deleteCommand->withFilesystem($filesystem);

        $commandResult = $this->commandBus->dispatch($deleteCommand);

        if (!$commandResult->isSuccessful()) {
            return new ApiErrorResponse('media-media-delete', $commandResult->messages());
        }

        return new ApiSuccessResponse();
    }
}
