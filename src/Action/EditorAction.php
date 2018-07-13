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

namespace KiwiSuite\Media\Action;

use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\Media\Exceptions\InvalidArgumentException;
use KiwiSuite\Media\Processor\EditorImageProcessor;
use KiwiSuite\Media\Entity\Media;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionSubManager;
use KiwiSuite\Media\Config\MediaConfig;
use KiwiSuite\Media\Repository\MediaRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class EditorAction implements MiddlewareInterface
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
     * @var ImageDefinitionSubManager
     */
    private $imageDefinitionSubManager;

    /**
     * @var array
     */
    private $data;

    /**
     * EditorAction constructor.
     * @param MediaRepository $mediaRepository
     * @param ImageDefinitionMapping $imageDefinitionMapping
     * @param ImageDefinitionSubManager $imageDefinitionSubManager
     */
    public function __construct(
        MediaRepository $mediaRepository,
        ImageDefinitionSubManager $imageDefinitionSubManager,
        MediaConfig $mediaConfig
    )
    {
        $this->mediaRepository = $mediaRepository;
        $this->mediaConfig = $mediaConfig;
        $this->imageDefinitionSubManager = $imageDefinitionSubManager;
    }


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->data = \json_decode($request->getBody()->getContents(),true);

        $requestParameters = $this->provideRequestParameters();
        $mediaParameters = $this->provideMediaParameters();
        $imageDefinitionParameters = $this->provideImageDefinitionParameters();

        $editorProcessor = new EditorImageProcessor($requestParameters, $mediaParameters, $imageDefinitionParameters);
        $editorProcessor->process();
        return new ApiSuccessResponse();
    }

    /**
     * @return array
     */
    private function provideRequestParameters()
    {
        $requestParameters = [
            'x'         => $this->data['x'],
            'y'         => $this->data['y'],
            'width'     => $this->data['width'],
            'height'    => $this->data['height'],
        ];
        return $requestParameters;
    }

    /**
     * @return array
     */
    private function provideMediaParameters()
    {
        $media = $this->mediaRepository->findBy(['id' => $this->data['id']])[0];
        $file = \getcwd() . '/data/media/' . $media->basePath() . $media->filename();
        $file = \getimagesize($file);
        $mediaParameter = [
            'width'     => $file[0],
            'height'    => $file[1],
            'basePath'  => $media->basePath(),
            'filename'  => $media->filename(),
            'driver'    => $this->mediaConfig->getDriver(),
        ];
        return $mediaParameter;
    }

    /**
     * @return array
     */
    private function provideImageDefinitionParameters()
    {
        if (!array_key_exists(
            $this->data['imageDefinition'],
            $this->imageDefinitionSubManager->getServiceManagerConfig()->getNamedServices())) {
            throw new InvalidArgumentException(
                sprintf('ImageDefinition: %s does not exist', $this->data['imageDefinition'])
            );
        }

        $definitionName = $this->data['imageDefinition'];
        $definition = $this->imageDefinitionSubManager->get($definitionName);
        $imageDefinitionParameters = [
            'name'      => $definition->getName(),
            'directory' => $definition->getDirectory(),
            'width'     => $definition->getWidth(),
            'height'    => $definition->getHeight(),
        ];
        return $imageDefinitionParameters;
    }


}