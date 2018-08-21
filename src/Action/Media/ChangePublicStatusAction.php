<?php
declare(strict_types=1);

namespace KiwiSuite\Media\Action\Media;


use KiwiSuite\Admin\Response\ApiErrorResponse;
use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\Media\Config\MediaConfig;
use KiwiSuite\Media\Entity\Media;
use KiwiSuite\Media\Repository\MediaRepository;
use KiwiSuite\Media\Resource\MediaResource;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ChangePublicStatusAction implements MiddlewareInterface
{
    /**
     * @var MediaRepository
     */
    private $mediaRepository;

    /**
     * @var MediaConfig
     */
    private $mediaConfig;

    /**
     * ChangePublicStatusAction constructor.
     * @param MediaConfig $mediaConfig
     * @param MediaRepository $mediaRepository
     */
    public function __construct(MediaRepository $mediaRepository, MediaConfig $mediaConfig)
    {
        $this->mediaRepository = $mediaRepository;
        $this->mediaConfig = $mediaConfig;
    }
    
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Media $media */
        $media = $this->mediaRepository->findOneBy(['id' => $request->getAttribute('id')]);

        if ($media === null) {
            return new ApiErrorResponse('given media Id does not exist');
        }
        $publicStatus = $request->getAttribute('publicStatus');
        settype($publicStatus,'boolean');

        if ($publicStatus === $media->publicStatus()) {
            return new ApiErrorResponse('the publicStatus is already set to '.$publicStatus);
        }

        $media->with('publicStatus',$publicStatus);
        $media->with('updatedAt', new \DateTime());

        $this->mediaRepository->save($media);

        return new ApiSuccessResponse('yay');
    }
}