<?php
/**
 * kiwi-suite/media (https://github.com/kiwi-suite/media)
 *
 * @package kiwi-suite/media
 * @see https://github.com/kiwi-suite/media
 * @copyright Copyright (c) 2010 - 2018 kiwi suite GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace KiwiSuite\Media\Action\Media;

use KiwiSuite\Admin\Response\ApiErrorResponse;
use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionSubManager;
use KiwiSuite\Media\Repository\MediaCreatedRepository;
use KiwiSuite\Media\Repository\MediaCropRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use KiwiSuite\Media\Repository\MediaRepository;
use KiwiSuite\Media\Entity\Media;

final class DeleteAction implements MiddlewareInterface
{
    /**
     * @var MediaRepository
     */
    private $mediaRepository;

    /**
     * @var MediaCropRepository
     */
    private $mediaCropRepository;

    /**
     * @var ImageDefinitionSubManager
     */
    private $imageDefinitionSubManager;
    /**
     * @var MediaCreatedRepository
     */
    private $mediaCreatedRepository;

    /**
     * DeleteAction constructor.
     * @param MediaCreatedRepository $mediaCreatedRepository
     * @param MediaRepository $mediaRepository
     * @param MediaCropRepository $mediaCropRepository
     * @param ImageDefinitionSubManager $imageDefinitionSubManager
     */
    public function __construct(MediaCreatedRepository $mediaCreatedRepository, MediaRepository $mediaRepository, MediaCropRepository $mediaCropRepository, ImageDefinitionSubManager $imageDefinitionSubManager)
    {
        $this->mediaRepository = $mediaRepository;
        $this->mediaCropRepository = $mediaCropRepository;
        $this->imageDefinitionSubManager = $imageDefinitionSubManager;
        $this->mediaCreatedRepository = $mediaCreatedRepository;
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

        $this->deleteFromStore($media);

        $this->mediaRepository->remove($media);

        $mediaCreated = $this->mediaCreatedRepository->findOneBy(['mediaId' => $media->id()]);
        if (!empty($mediaCreated)) {
            $this->mediaCreatedRepository->remove($mediaCreated);
        }

        return new ApiSuccessResponse();
    }

    /**
     * @param Media $media
     */
    private function deleteFromStore(Media $media)
    {
        $path = $media->basePath() . $media->filename();

        \unlink(\getcwd() . '/data/media/' . $path);

        foreach ($this->imageDefinitionSubManager->getServiceManagerConfig()->getNamedServices() as $namedService => $namespace) {
            if (\file_exists(\getcwd() . '/data/media/img/' . $namedService . '/' . $path)) {
                \unlink(\getcwd() . '/data/media/img/' . $namedService . '/' . $path);
            }
        }
    }
}
