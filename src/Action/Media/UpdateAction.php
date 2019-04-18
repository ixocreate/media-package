<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Package\Action\Media;

use Ixocreate\Admin\Package\Response\ApiErrorResponse;
use Ixocreate\Admin\Package\Response\ApiSuccessResponse;
use Ixocreate\CommandBus\CommandBus;
use Ixocreate\Media\Package\Command\Media\UpdateCommand;
use Ixocreate\Media\Package\Entity\Media;
use Ixocreate\Media\Package\Repository\MediaRepository;
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
     * UpdateAction constructor.
     * @param MediaRepository $mediaRepository
     * @param CommandBus $commandBus
     */
    public function __construct(MediaRepository $mediaRepository, CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
        $this->mediaRepository = $mediaRepository;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Media $media */
        $media = $this->mediaRepository->findOneBy(['id' => $request->getAttribute('id')]);

        if (empty($media)) {
            return new ApiErrorResponse('given media Id does not exist');
        }

        $data = $request->getParsedBody();
        $publicStatus = $data['publicStatus'];
        $newFilename = $data['newFilename'];

        /** @var UpdateCommand $command */
        $command = $this->commandBus->create(UpdateCommand::class, []);
        $command = $command->withMedia($media);
        $command = $command->withPublicStatus($publicStatus);
        $command = $command->withNewFilename($newFilename);
        $result = $this->commandBus->dispatch($command);

        if (!$result->isSuccessful()) {
            return new ApiErrorResponse('media-media-update', $result->messages());
        }

        return new ApiSuccessResponse();
    }
}
