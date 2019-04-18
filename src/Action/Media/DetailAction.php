<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Action\Media;

use Ixocreate\Admin\Response\ApiErrorResponse;
use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\Media\ImageDefinitionInterface;
use Ixocreate\Entity\Type\Type;
use Ixocreate\Filesystem\Storage\StorageSubManager;
use Ixocreate\Media\Handler\ImageHandler;
use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\Exception\InvalidConfigException;
use Ixocreate\Media\ImageDefinition\ImageDefinitionSubManager;
use Ixocreate\Media\MediaPaths;
use Ixocreate\Media\Repository\MediaCropRepository;
use Ixocreate\Media\Type\MediaType;
use Ixocreate\Media\Uri\MediaUri;
use League\Flysystem\FilesystemInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class DetailAction implements MiddlewareInterface
{
    /**
     * @var MediaUri
     */
    private $uri;

    /**
     * @var ImageHandler
     */
    private $imageHandler;

    /**
     * @var ImageDefinitionSubManager
     */
    private $imageDefinitionSubManager;

    /**
     * @var MediaCropRepository
     */
    private $mediaCropRepository;

    /**
     * @var StorageSubManager
     */
    private $storageSubManager;

    /**
     * @var FilesystemInterface
     */
    private $storage;

    /**
     * DetailAction constructor.
     *
     * @param MediaUri $uri
     * @param ImageHandler $imageHandler
     * @param ImageDefinitionSubManager $imageDefinitionSubManager
     * @param MediaCropRepository $mediaCropRepository
     * @param StorageSubManager $storageSubManager
     */
    public function __construct(
        MediaUri $uri,
        ImageHandler $imageHandler,
        ImageDefinitionSubManager $imageDefinitionSubManager,
        MediaCropRepository $mediaCropRepository,
        StorageSubManager $storageSubManager
    ) {
        $this->uri = $uri;
        $this->imageHandler = $imageHandler;
        $this->imageDefinitionSubManager = $imageDefinitionSubManager;
        $this->mediaCropRepository = $mediaCropRepository;
        $this->storageSubManager = $storageSubManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @throws \League\Flysystem\FileNotFoundException
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->storageSubManager->has('media')) {
            throw new InvalidConfigException('Storage Config not set');
        }

        $this->storage = $this->storageSubManager->get('media');

        /** @var MediaType $media */
        $media = Type::create($request->getAttribute("id"), MediaType::class);
        if (empty($media->value())) {
            return new ApiErrorResponse('given_media_Id_does_not_exist');
        }

        $isCropable = $this->imageHandler->isResponsible($media->value());

        $result = [
            'media' => $media->jsonSerialize(),
            'isCropable' => $isCropable,
        ];

        $media = $media->value();

        $mediaCropArray = $this->mediaCropRepository->findBy(['mediaId' => ($media->id())]);

        if ($isCropable) {
            $definitions = $this->determineDefinitions($media, $mediaCropArray);
            $result['definitions'] = $definitions;
        }

        return new ApiSuccessResponse($result);
    }

    /**
     * @param Media $media
     * @param ImageDefinitionInterface $imageDefinition
     * @throws \League\Flysystem\FileNotFoundException
     * @return bool
     */
    private function checkValidSize(Media $media, ImageDefinitionInterface $imageDefinition): bool
    {
        $state = false;

        $mediaPath = $media->publicStatus() ? MediaPaths::PUBLIC_PATH : MediaPaths::PRIVATE_PATH;

        $file = $this->storage->read($mediaPath . $media->basePath() . $media->filename());

        $size = \getimagesizefromstring($file);

        $width = $size[0];
        $height = $size[1];

        if ($width === $height) {
            if ($width >= $imageDefinition->width() && $width >= $imageDefinition->height()) {
                $state = true;
            }
        }

        if ($width >= $imageDefinition->width() && $height >= $imageDefinition->height()) {
            $state = true;
        }

        return $state;
    }

    /**
     * @param Media $media
     * @param array $mediaCropArray
     * @throws \League\Flysystem\FileNotFoundException
     * @return array
     */
    private function determineDefinitions(Media $media, array $mediaCropArray)
    {
        $definitions = [];

        foreach ($this->imageDefinitionSubManager->getServices() as $key => $name) {
            $imageDefinition = $this->imageDefinitionSubManager->get($name);
            $validSize = $this->checkValidSize($media, $imageDefinition);
            $definitions[] = ['name' => $imageDefinition::serviceName(), 'isCropable' => $validSize, 'cropParameter' => ''];

            foreach ($mediaCropArray as $mediaCrop) {
                if ($mediaCrop->cropParameters() != null && $imageDefinition::serviceName() === $mediaCrop->imageDefinition()) {
                    $definitions[$key]['cropParameter'] = $mediaCrop->cropParameters();
                }
            }
        }
        return $definitions;
    }
}
