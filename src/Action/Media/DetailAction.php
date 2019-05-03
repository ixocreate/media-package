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
use Ixocreate\Filesystem\FilesystemManager;
use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\Exception\InvalidConfigException;
use Ixocreate\Media\Handler\ImageHandler;
use Ixocreate\Media\ImageDefinition\ImageDefinitionSubManager;
use Ixocreate\Media\ImageDefinitionInterface;
use Ixocreate\Media\MediaPaths;
use Ixocreate\Media\Repository\MediaCropRepository;
use Ixocreate\Media\Schema\Type\MediaType;
use Ixocreate\Media\Uri\MediaUri;
use Ixocreate\Schema\Type\Type;
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
     * @var FilesystemManager
     */
    private $filesystemManager;

    /**
     * DetailAction constructor.
     *
     * @param MediaUri $uri
     * @param ImageHandler $imageHandler
     * @param ImageDefinitionSubManager $imageDefinitionSubManager
     * @param MediaCropRepository $mediaCropRepository
     * @param FilesystemManager $filesystemManager
     */
    public function __construct(
        MediaUri $uri,
        ImageHandler $imageHandler,
        ImageDefinitionSubManager $imageDefinitionSubManager,
        MediaCropRepository $mediaCropRepository,
        FilesystemManager $filesystemManager
    ) {
        $this->uri = $uri;
        $this->imageHandler = $imageHandler;
        $this->imageDefinitionSubManager = $imageDefinitionSubManager;
        $this->mediaCropRepository = $mediaCropRepository;
        $this->filesystemManager = $filesystemManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @throws \League\Flysystem\FileNotFoundException
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->filesystemManager->has('media')) {
            throw new InvalidConfigException('Storage Config not set');
        }

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

        $file = $this->filesystemManager->get("media")->read($mediaPath . $media->basePath() . $media->filename());

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
            $definitions[] = [
                'name' => $imageDefinition::serviceName(),
                'isCropable' => $validSize,
                'cropParameter' => '',
            ];

            foreach ($mediaCropArray as $mediaCrop) {
                if ($mediaCrop->cropParameters() != null && $imageDefinition::serviceName() === $mediaCrop->imageDefinition()) {
                    $definitions[$key]['cropParameter'] = $mediaCrop->cropParameters();
                }
            }
        }
        return $definitions;
    }
}
