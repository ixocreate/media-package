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

namespace KiwiSuite\Media\Action\Image;


use Assert\Assertion;
use KiwiSuite\Admin\Response\ApiErrorResponse;
use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\Media\Config\MediaConfig;
use KiwiSuite\Media\Entity\Media;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionInterface;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionSubManager;
use KiwiSuite\Media\Processor\EditorImageProcessor;
use KiwiSuite\Media\Repository\MediaRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class EditorAction implements MiddlewareInterface
{
    /**
     * @var MediaConfig
     */
    private $mediaConfig;

    /**
     * @var ImageDefinitionSubManager
     */
    private $imageDefinitionSubManager;

    /**
     * @var MediaRepository
     */
    private $mediaRepository;

    /**
     * NewEditorAction constructor.
     * @param MediaRepository $mediaRepository
     * @param MediaConfig $mediaConfig
     * @param ImageDefinitionSubManager $imageDefinitionSubManager
     */
    public function __construct(MediaRepository $mediaRepository, MediaConfig $mediaConfig, ImageDefinitionSubManager $imageDefinitionSubManager)
    {
        $this->mediaRepository = $mediaRepository;
        $this->mediaConfig = $mediaConfig;
        $this->imageDefinitionSubManager = $imageDefinitionSubManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \Assert\AssertionFailedException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!Assertion::isJsonString($request->getBody()->getContents())) {
            return new ApiErrorResponse('data need to be Json');
        }

        if (empty($request->getBody()->getContents())) {
            return new ApiErrorResponse('no parameters passed to editor');
        }

        $requestData = json_decode($request->getBody()->getContents(), true);

        $media = $this->media($requestData);
        $imageDefinition = $this->imageDefinition($requestData);

        (new EditorImageProcessor($requestData, $imageDefinition, $media, $this->mediaConfig))->process();

        return new ApiSuccessResponse();
    }

    /**
     * @param array $requestData
     * @return ImageDefinitionInterface
     */
    private function imageDefinition(array $requestData): ImageDefinitionInterface
    {
        $imageDefinition = $this->imageDefinitionSubManager->get(lcfirst($requestData['imageDefinition']));

        return $imageDefinition;
    }

    /**
     * @param array $requestData
     * @return Media
     */
    private function media(array $requestData): Media
    {
        return $this->mediaRepository->find(['id' => $requestData['id']]);
    }
}