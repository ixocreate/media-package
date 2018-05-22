<?php
/**
 * Created by PhpStorm.
 * User: afriedrich
 * Date: 16.05.18
 * Time: 16:45
 */

namespace KiwiSuite\Media\Action;


use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\Media\Editor\EditorHandler;
use KiwiSuite\Media\Entity\Media;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionMapping;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionSubManager;
use KiwiSuite\Media\MediaConfig;
use KiwiSuite\Media\Repository\MediaRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class EditorAction implements MiddlewareInterface
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
     * @var ImageDefinitionMapping
     */
    private $imageDefinitionMapping;

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
    public function __construct(MediaRepository $mediaRepository, ImageDefinitionMapping $imageDefinitionMapping, ImageDefinitionSubManager $imageDefinitionSubManager, MediaConfig $mediaConfig)
    {
        $this->mediaRepository = $mediaRepository;
        $this->mediaConfig = $mediaConfig;
        $this->imageDefinitionMapping = $imageDefinitionMapping;
        $this->imageDefinitionSubManager = $imageDefinitionSubManager;
    }


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->data = json_decode($request->getBody()->getContents(),true);

        $requestParameters = $this->provideRequestParameters();
        $mediaParameters = $this->provideMediaParameters();
        $imageDefinitionParameters = $this->provideImageDefinitionParameters();
        $mediaConfig = $this->mediaConfig;

        $editorHandler = new EditorHandler($requestParameters, $mediaParameters, $imageDefinitionParameters, $mediaConfig);
        $editorHandler->handle();
//        return new ApiSuccessResponse();
    }

    /**
     * @return array
     */
    private function provideRequestParameters()
    {
        $requestParameters = [
            'xPosition' => $this->data['x'],
            'yPosition' => $this->data['y'],
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
        $file = getcwd() . '/data/media/' . $media->basePath() . $media->filename();
        $file = getimagesize($file);
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
        $mapping = $this->imageDefinitionMapping->getMapping();
        $definition = \trim(\ucfirst($this->data['imageDefinition']));
        $definition = $this->imageDefinitionSubManager->get($mapping[$definition]);
        $imageDefinitionParameters = [
            'name'      => $definition->getName(),
            'namespace' => $mapping[$definition->getName()],
            'fit'       => $definition->getFit(),
            'width'     => $definition->getWidth(),
            'height'    => $definition->getHeight()
        ];
        return $imageDefinitionParameters;
    }


}