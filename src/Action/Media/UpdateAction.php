<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Action\Media;

use Ixocreate\Admin\Response\ApiErrorResponse;
use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\CommandBus\CommandBus;
use Ixocreate\Filesystem\FilesystemManager;
use Ixocreate\Media\Command\Media\UpdateCommand;
use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\Exception\InvalidConfigException;
use Ixocreate\Media\Repository\MediaRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class UpdateAction implements MiddlewareInterface
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var MediaRepository
     */
    private $mediaRepository;

    /**
     * @var FilesystemManager
     */
    private $filesystemManager;

    /**
     * UpdateAction constructor.
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
        $this->commandBus = $commandBus;
        $this->mediaRepository = $mediaRepository;
        $this->filesystemManager = $filesystemManager;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Media $media */
        $media = $this->mediaRepository->findOneBy(['id' => $request->getAttribute('id')]);

        if (empty($media)) {
            return new ApiErrorResponse('given media Id does not exist');
        }

        if (!$this->filesystemManager->has('media')) {
            throw new InvalidConfigException('Storage Config not set');
        }

        $filesystem = $this->filesystemManager->get('media');

        $data = $request->getParsedBody();
        $publicStatus = $data['publicStatus'];
        $newFilename = $data['newFilename'];

        /** @var UpdateCommand $updateCommand */
        $updateCommand = $this->commandBus->create(UpdateCommand::class, []);
        $updateCommand = $updateCommand->withMedia($media);
        $updateCommand = $updateCommand->withPublicStatus($publicStatus);
        $updateCommand = $updateCommand->withNewFilename($newFilename);
        $updateCommand = $updateCommand->withFilesystem($filesystem);
        $commandResult = $this->commandBus->dispatch($updateCommand);

        if (!$commandResult->isSuccessful()) {
            return new ApiErrorResponse('media-media-update', $commandResult->messages());
        }

        return new ApiSuccessResponse();
    }
}
