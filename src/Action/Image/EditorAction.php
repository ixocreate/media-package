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
use KiwiSuite\Entity\Entity\EntityInterface;
use KiwiSuite\Media\Config\MediaConfig;
use KiwiSuite\Media\Entity\Media;
use KiwiSuite\Media\Entity\MediaCrop;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionInterface;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionSubManager;
use KiwiSuite\Media\Processor\EditorImageProcessor;
use KiwiSuite\Media\Repository\MediaCropRepository;
use KiwiSuite\Media\Repository\MediaRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;

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
     * @var MediaCropRepository
     */
    private $mediaCropRepository;

    /**
     * EditorAction constructor.
     * @param MediaRepository $mediaRepository
     * @param MediaConfig $mediaConfig
     * @param ImageDefinitionSubManager $imageDefinitionSubManager
     * @param MediaCropRepository $mediaCropRepository
     */
    public function __construct(
        MediaRepository $mediaRepository,
        MediaConfig $mediaConfig,
        ImageDefinitionSubManager $imageDefinitionSubManager,
        MediaCropRepository $mediaCropRepository
    )
    {
        $this->mediaRepository = $mediaRepository;
        $this->mediaConfig = $mediaConfig;
        $this->imageDefinitionSubManager = $imageDefinitionSubManager;
        $this->mediaCropRepository = $mediaCropRepository;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \Assert\AssertionFailedException
     * @throws \Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!Assertion::isJsonString($request->getBody()->getContents())) {
            return new ApiErrorResponse('data_need_to_be_json');
        }

        if (empty($request->getBody()->getContents())) {
            return new ApiErrorResponse('no_parameters_passed_to_editor');
        }

        $requestData = \json_decode($request->getBody()->getContents(), true);

        $media = $this->media($requestData);
        $imageDefinition = $this->imageDefinition($requestData);

        $entity = null;

        if (!empty($this->mediaCropRepository->findOneBy(['mediaId' => $media->id(), 'imageDefinition' => $imageDefinition::serviceName()]))) {
            /** @var EntityInterface $entity */
            $entity = $this->mediaCropRepository->findOneBy(['mediaId' => $media->id(), 'imageDefinition' => $imageDefinition::serviceName()]);

            if ($entity::getDefinitions()->has('updatedAt')) {
                $entity = $entity->with('updatedAt', new \DateTime());
            }

            if ($entity::getDefinitions()->has('cropParameters')) {
                $entity = $entity->with('cropParameters', $requestData['crop']);
            }

        }

        if (empty($this->mediaCropRepository->findOneBy(['mediaId' => $media->id(), 'imageDefinition' => $imageDefinition::serviceName()]))) {

            $entity = new MediaCrop([
                'id' => Uuid::uuid4(),
                'mediaId' => $media->id(),
                'imageDefinition' => $imageDefinition::serviceName(),
                'cropParameters' => $requestData['crop'],
                'createdAt' => new \DateTimeImmutable(),
                'updatedAt' => new \DateTimeImmutable(),
            ]);

        }

        (new EditorImageProcessor($requestData['crop'], $imageDefinition, $media, $this->mediaConfig))->process();

        $this->mediaCropRepository->save($entity);

        return new ApiSuccessResponse();
    }

    /**
     * @param array $requestData
     * @return ImageDefinitionInterface
     */
    private function imageDefinition(array $requestData): ImageDefinitionInterface
    {
        //TODO EXIST CHECK
        return $this->imageDefinitionSubManager->get($requestData['imageDefinition']);
    }

    /**
     * @param array $requestData
     * @return Media
     */
    private function media(array $requestData): Media
    {
        //TODO EXIST CHECK
        return $this->mediaRepository->find($requestData['id']);
    }
}