<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Action\Image;

use Ixocreate\Admin\Response\ApiErrorResponse;
use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\CommandBus\CommandBus;
use Ixocreate\Filesystem\Storage\StorageSubManager;
use Ixocreate\Media\Command\Image\EditorCommand;
use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\ImageDefinition\ImageDefinitionSubManager;
use Ixocreate\Media\ImageDefinitionInterface;
use Ixocreate\Media\Repository\MediaCropRepository;
use Ixocreate\Media\Repository\MediaRepository;
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
     * @var MediaCropRepository
     */
    private $mediaCropRepository;

    /**
     * @var StorageSubManager
     */
    private $storageSubManager;

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * EditorAction constructor.
     *
     * @param CommandBus $commandBus
     * @param MediaRepository $mediaRepository
     * @param MediaConfig $mediaConfig
     * @param ImageDefinitionSubManager $imageDefinitionSubManager
     * @param MediaCropRepository $mediaCropRepository
     * @param StorageSubManager $storageSubManager
     */
    public function __construct(
        CommandBus $commandBus,
        MediaRepository $mediaRepository,
        MediaConfig $mediaConfig,
        ImageDefinitionSubManager $imageDefinitionSubManager,
        MediaCropRepository $mediaCropRepository,
        StorageSubManager $storageSubManager
    ) {
        $this->mediaRepository = $mediaRepository;
        $this->mediaConfig = $mediaConfig;
        $this->imageDefinitionSubManager = $imageDefinitionSubManager;
        $this->mediaCropRepository = $mediaCropRepository;
        $this->storageSubManager = $storageSubManager;
        $this->commandBus = $commandBus;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @throws \Exception
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (empty($request->getBody()->getContents())) {
            return new ApiErrorResponse('no_parameters_passed_to_editor');
        }

        $requestData = \json_decode($request->getBody()->getContents(), true);
        if ($requestData === null) {
            return new ApiErrorResponse('data_need_to_be_json');
        }

        /** @var Media $media */
        $media = $this->media($requestData);
        /** @var ImageDefinitionInterface $imageDefinition */
        $imageDefinition = $this->imageDefinition($requestData);

        $data = [
            'media' => $media,
            'imageDefinition' => $imageDefinition,
            'requestData' => $requestData,
        ];

        $commandResult = $this->commandBus->command(EditorCommand::class, $data);

        if (!$commandResult->isSuccessful()) {
            return new ApiErrorResponse('media-media-delete', $commandResult->messages());
        }

        return new ApiSuccessResponse();
    }

    /**
     * @param array $requestData
     * @return ApiErrorResponse|mixed
     */
    private function imageDefinition(array $requestData)
    {
        if (!$this->imageDefinitionSubManager->has($requestData['imageDefinition'])) {
            return new ApiErrorResponse('Given ImageDefinition does not exist');
        }

        return $this->imageDefinitionSubManager->get($requestData['imageDefinition']);
    }

    /**
     * @param array $requestData
     * @return ApiErrorResponse|object|null
     */
    private function media(array $requestData)
    {
        if ($this->mediaRepository->count(['id' => $requestData['id']]) === null) {
            return new ApiErrorResponse('Given Media Id does not exist');
        }

        return $this->mediaRepository->find($requestData['id']);
    }
}
