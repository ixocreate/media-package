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
use KiwiSuite\Media\ImageDefinition\ImageDefinitionInterface;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionSubManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use KiwiSuite\Media\Repository\MediaRepository;
use KiwiSuite\Media\Config\MediaConfig;
use KiwiSuite\Media\Entity\Media;

final class DeleteAction implements MiddlewareInterface
{
    /**
     * @var MediaRepository
     */
    private $mediaRepository;

    /**
     * @var ImageDefinitionSubManager
     */
    private $imageDefinitionSubManager;

    /**
     * ChangePublicStatusAction constructor.
     * @param MediaRepository $mediaRepository
     */
    public function __construct(MediaRepository $mediaRepository, ImageDefinitionSubManager $imageDefinitionSubManager)
    {
        $this->mediaRepository = $mediaRepository;
        $this->imageDefinitionSubManager = $imageDefinitionSubManager;
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

        if ($media === null) {
            return new ApiErrorResponse('given media Id does not exist');
        }

        $this->deleteFromStore($media);

        $this->mediaRepository->remove($media);
        $this->mediaRepository->flush($media);

        return new ApiSuccessResponse();
    }

    /**
     * @param Media $media
     */
    private function deleteFromStore(Media $media)
    {
        $path = $media->basePath() . $media->filename();

        unlink(getcwd() . '/data/media/'. $path);

        foreach ($this->imageDefinitionSubManager->getServiceManagerConfig()->getNamedServices() as $namedService => $namespace) {
            unlink(getcwd() . '/data/media/img/' . $namedService . '/' . $path);
        }
    }

}