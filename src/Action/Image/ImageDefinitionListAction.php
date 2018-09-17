<?php

namespace KiwiSuite\Media\Action\Image;

use KiwiSuite\Media\ImageDefinition\ImageDefinitionInterface;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionSubManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;


final class ImageDefinitionListAction implements MiddlewareInterface
{
    /**
     * @var ImageDefinitionSubManager
     */
    private $imageDefinitionSubManager;

    /**
     * ImageDefinitionAction constructor.
     * @param ImageDefinitionSubManager $imageDefinitionSubManager
     */
    public function __construct(ImageDefinitionSubManager $imageDefinitionSubManager)
    {
        $this->imageDefinitionSubManager = $imageDefinitionSubManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $list = [];

        foreach ($this->imageDefinitionSubManager->getServiceManagerConfig()->getNamedServices() as $imageDefinition) {
            /** @var $imageDefinition ImageDefinitionInterface */
            $list[] = $imageDefinition::serviceName();
        }

        json_encode($list);

        return new JsonResponse($list);
    }
}