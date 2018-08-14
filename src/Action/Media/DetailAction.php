<?php
declare(strict_types=1);

namespace KiwiSuite\Media\Action\Media;

use KiwiSuite\Admin\Response\ApiDetailResponse;
use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\Contract\Resource\AdminAwareInterface;
use KiwiSuite\Contract\Resource\ResourceInterface;
use KiwiSuite\Database\Repository\Factory\RepositorySubManager;
use KiwiSuite\Entity\Entity\EntityInterface;
use KiwiSuite\Schema\Builder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class DetailAction implements MiddlewareInterface
{
    private $builder;

    private $repositorySubManager;

    public function __construct(Builder $builder, RepositorySubManager $repositorySubManager)
    {
        $this->builder = $builder;
        $this->repositorySubManager = $repositorySubManager;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var AdminAwareInterface $resource */
        $resource = $request->getAttribute(ResourceInterface::class);

        /** @var RepositorySubManager $repository */
        $repository = $this->repositorySubManager->get($resource->repository());

        /** @var EntityInterface $entity */
        $entity = $repository->find($request->getAttribute("id"));

        return new ApiDetailResponse($resource,$entity->toPublicArray(),$resource->updateSchema($this->builder),[]);
    }

}