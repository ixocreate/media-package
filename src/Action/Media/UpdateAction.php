<?php
declare(strict_types=1);

namespace KiwiSuite\Media\Action\Media;


use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\Media\Repository\MediaRepository;
use KiwiSuite\Schema\Builder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class UpdateAction implements MiddlewareInterface
{
    private $mediaRepository;

    private $builder;

    public function __construct(MediaRepository $mediaRepository, Builder $builder)
    {
        $this->mediaRepository = $mediaRepository;
        $this->builder = $builder;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return new ApiSuccessResponse();
    }
}