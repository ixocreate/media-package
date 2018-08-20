<?php
declare(strict_types=1);

namespace KiwiSuite\Media\Action\Media;


use KiwiSuite\Admin\Response\ApiErrorResponse;
use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\Media\Config\MediaConfig;
use KiwiSuite\Media\Entity\Media;
use KiwiSuite\Media\Repository\MediaRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ChangePublicStatusAction implements MiddlewareInterface
{
    /**
     * @var MediaConfig
     */
    private $mediaConfig;

    /**
     * @var MediaRepository
     */
    private $mediaRepository;

    /**
     * ChangePublicStatusAction constructor.
     * @param MediaConfig $mediaConfig
     * @param MediaRepository $mediaRepository
     */
    public function __construct(MediaConfig $mediaConfig, MediaRepository $mediaRepository)
    {
        $this->mediaConfig = $mediaConfig;
        $this->mediaRepository = $mediaRepository;
    }
    
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->mediaConfig->publicStatus()) {
            return new ApiErrorResponse();
        }
        /** @var Media $media */
        $media = $this->mediaRepository->findOneBy(['id' => $request->getAttribute('id')]);

        if ($media === null) {
            return new ApiErrorResponse();
        }

        if ($request->getAttribute('publicStatus') === $media->publicStatus()) {
            return new ApiErrorResponse();
        }

        $media->with('publicStatus',$request->getAttribute('publicStatus'));
        $media->with('updatedAt', new \DateTime());

        $this->mediaRepository->save($media);

        return new ApiSuccessResponse();
    }
}