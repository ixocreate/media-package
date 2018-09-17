<?php

namespace KiwiSuite\Media\Action\Image;


use KiwiSuite\Admin\Response\ApiErrorResponse;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionInterface;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionSubManager;
use KiwiSuite\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;

final class ImageDefinitionDetailAction implements MiddlewareInterface
{
    private $imageDefinitionSubManager;

    public function __construct(ImageDefinitionSubManager $imageDefinitionSubManager)
    {
        $this->imageDefinitionSubManager = $imageDefinitionSubManager;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $name = $request->getAttribute('name');

        try {
            /** @var ImageDefinitionInterface $imageDefinition */
            $imageDefinition = $this->imageDefinitionSubManager->get($name);
        } catch (ServiceNotFoundException $exception) {
            return new ApiErrorResponse($exception->getMessage());
        }

        $details = [
            'width' => $imageDefinition->width(),
            'height' => $imageDefinition->height(),
            'mode' => $imageDefinition->mode(),
            'upscale' => $imageDefinition->upscale(),
            'directory' => $imageDefinition->directory()
        ];

        json_encode($details);

        return new JsonResponse($details);
    }
}