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
        $list = [
            'name' => [],
            'width' => [],
            'height' => []
        ];

        $result = [];

        foreach ($this->imageDefinitionSubManager->getServiceManagerConfig()->getNamedServices() as $name) {
            $imageDefinition = $this->imageDefinitionSubManager->get($name);
            /** @var $imageDefinition ImageDefinitionInterface */
            $list['name'] = $imageDefinition::serviceName();
            $list['width'] = $imageDefinition->width();
            $list['height'] = $imageDefinition->height();
            $result[] = $list;
        }

        json_encode($result);

        return new JsonResponse($result);
    }
}