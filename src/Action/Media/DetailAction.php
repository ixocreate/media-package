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
use Ixocreate\Media\Entity\MediaDefinitionInfo;
use Ixocreate\Media\Exception\InvalidConfigException;
use Ixocreate\Media\Handler\ImageHandler;
use Ixocreate\Media\ImageDefinition\ImageDefinitionSubManager;
use Ixocreate\Media\ImageDefinitionInterface;
use Ixocreate\Media\MediaPaths;
use Ixocreate\Media\Repository\MediaDefinitionInfoRepository;
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
     * @var MediaDefinitionInfoRepository
     */
    private $mediaDefinitionInfoRepository;

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
     * @param MediaDefinitionInfoRepository $mediaDefinitionInfoRepository
     * @param FilesystemManager $filesystemManager
     */
    public function __construct(
        MediaUri $uri,
        ImageHandler $imageHandler,
        ImageDefinitionSubManager $imageDefinitionSubManager,
        MediaDefinitionInfoRepository $mediaDefinitionInfoRepository,
        FilesystemManager $filesystemManager
    ) {
        $this->uri = $uri;
        $this->imageHandler = $imageHandler;
        $this->imageDefinitionSubManager = $imageDefinitionSubManager;
        $this->mediaDefinitionInfoRepository = $mediaDefinitionInfoRepository;
        $this->filesystemManager = $filesystemManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->filesystemManager->has('media')) {
            throw new InvalidConfigException('Filesystem Config not set');
        }

        /** @var MediaType $media */
        $media = Type::create($request->getAttribute("id"), MediaType::class);
        if (empty($media->value())) {
            return new ApiErrorResponse('given_media_Id_does_not_exist');
        }

        // Check if MimeType is valid to be cropped
        $isCropable = $this->imageHandler->isResponsible($media->value());

        $result = [
            'media' => $media->jsonSerialize(),
            'isCropable' => $isCropable,
        ];

        $media = $media->value();

        if ($isCropable) {
            $definitions = $this->determineDefinitions($media);
            $result['definitions'] = $definitions;
        }

        return new ApiSuccessResponse($result);
    }

    /**
     * @param Media $media
     * @param ImageDefinitionInterface $imageDefinition
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
     * @return array
     */
    private function determineDefinitions(Media $media): array
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

            /** @var MediaDefinitionInfo $mediaDefinitionInfo */
            $mediaDefinitionInfo = $this->mediaDefinitionInfoRepository->findBy(['mediaId' => $media->id(), 'imageDefinition' => $imageDefinition::serviceName()])[0];

            if ($mediaDefinitionInfo->cropParameters() !== null) {
                $definitions[$key]['cropParameter'] = $mediaDefinitionInfo->cropParameters();
            }

        }
        return $definitions;
    }
}
