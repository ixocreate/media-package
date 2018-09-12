<?php
declare(strict_types=1);

namespace KiwiSuite\Media\Action\Media;

use KiwiSuite\Admin\Response\ApiDetailResponse;
use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\Contract\Resource\AdminAwareInterface;
use KiwiSuite\Contract\Resource\ResourceInterface;
use KiwiSuite\Database\Repository\Factory\RepositorySubManager;
use KiwiSuite\Entity\Entity\EntityInterface;
use KiwiSuite\Media\Repository\MediaRepository;
use KiwiSuite\Schema\Builder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use KiwiSuite\Media\Entity\Media;
use Zend\Diactoros\Response\JsonResponse;

final class DetailAction implements MiddlewareInterface
{
    private $mediaRepository;

    public function __construct(MediaRepository $mediaRepository)
    {
        $this->mediaRepository = $mediaRepository;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Media $media */
        $media = $this->mediaRepository->findOneBy(['id' => $request->getAttribute('id')]);

        if ($media === null) {
            return new ApiErrorResponse('given media Id does not exist');
        }

        $details = [
            'id' => $media->id(),
            'filename' => $media->filename(),
            'basePath' => $media->basePath(),
            'mimeType' => $media->mimeType(),
            'size' => $media->size(),
            'publicStatus' => $media->publicStatus(),
            'createdAt' => $media->createdAt(),
            'updatedAt' => $media->updatedAt(),
        ];

        json_encode($details);

        return new JsonResponse($details);
    }

}