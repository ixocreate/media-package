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
use Ixocreate\Media\Command\Media\DeleteCommand;
use Ixocreate\Media\Entity\Media;
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
     * DeleteAction constructor.
     *
     * @param MediaRepository $mediaRepository
     * @param CommandBus $commandBus
     */
    public function __construct(MediaRepository $mediaRepository, CommandBus $commandBus)
    {
        $this->mediaRepository = $mediaRepository;
        $this->commandBus = $commandBus;
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

        $commandResult = $this->commandBus->command(DeleteCommand::class, ['media' => $media]);

        if (!$commandResult->isSuccessful()) {
            return new ApiErrorResponse('media-media-delete', $commandResult->messages());
        }

        return new ApiSuccessResponse();
    }
}
