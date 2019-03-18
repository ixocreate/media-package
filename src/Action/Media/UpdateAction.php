<?php
declare(strict_types=1);

namespace Ixocreate\Media\Action\Media;


use Ixocreate\Admin\Response\ApiErrorResponse;
use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\CommandBus\CommandBus;
use Ixocreate\Media\Command\UpdateCommand;
use Ixocreate\Media\Config\MediaConfig;
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
     * UpdateAction constructor.
     * @param CommandBus $commandBus
     */
    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();
        $data['id'] = $request->getAttribute('id');

        $command = $this->commandBus->command(UpdateCommand::class, $data);

        if (!$command->isSuccessful()) {
            return new ApiErrorResponse($command->messages());
        }

        return new ApiSuccessResponse();
    }
}