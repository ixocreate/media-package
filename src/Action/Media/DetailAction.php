<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Action\Media;

use Ixocreate\Admin\Response\ApiErrorResponse;
use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\Entity\MediaDefinitionInfo;
use Ixocreate\Media\Handler\ImageHandler;
use Ixocreate\Media\ImageDefinition\ImageDefinitionInterface;
use Ixocreate\Media\ImageDefinition\ImageDefinitionSubManager;
use Ixocreate\Media\Repository\MediaDefinitionInfoRepository;
use Ixocreate\Media\Schema\Type\MediaType;
use Ixocreate\Schema\Type\Type;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class DetailAction implements MiddlewareInterface
{
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
     * DetailAction constructor.
     *
     * @param ImageHandler $imageHandler
     * @param ImageDefinitionSubManager $imageDefinitionSubManager
     * @param MediaDefinitionInfoRepository $mediaDefinitionInfoRepository
     */
    public function __construct(
        ImageHandler $imageHandler,
        ImageDefinitionSubManager $imageDefinitionSubManager,
        MediaDefinitionInfoRepository $mediaDefinitionInfoRepository
    ) {
        $this->imageHandler = $imageHandler;
        $this->imageDefinitionSubManager = $imageDefinitionSubManager;
        $this->mediaDefinitionInfoRepository = $mediaDefinitionInfoRepository;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var MediaType $media */
        $media = Type::create($request->getAttribute('id'), MediaType::class);
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
     * @return array
     */
    private function determineDefinitions(Media $media): array
    {
        $definitions = [];

        $width = $media->metaData()['width'];
        $height = $media->metaData()['height'];

        foreach ($this->imageDefinitionSubManager->services() as $key => $name) {
            /** @var ImageDefinitionInterface $imageDefinition */
            $imageDefinition = $this->imageDefinitionSubManager->get($name);
            $validSize = $this->checkValidSize($imageDefinition, $width, $height);
            $definitions[] = [
                'name' => $imageDefinition::serviceName(),
                'mode' => $imageDefinition->mode(),
                'isCropable' => $validSize,
                'cropParameter' => '',
            ];

            try {
                /** @var MediaDefinitionInfo $mediaDefinitionInfo */
                $mediaDefinitionInfo = $this->mediaDefinitionInfoRepository->find(['mediaId' => $media->id(), 'imageDefinition' => $imageDefinition::serviceName()]);

                if ($mediaDefinitionInfo !== null && $mediaDefinitionInfo->cropParameters() !== null) {
                    $definitions[$key]['cropParameter'] = $mediaDefinitionInfo->cropParameters();
                }
            } catch (\Exception $exception) {
                unset($definitions[$key]);
                continue;
            }
        }
        return $definitions;
    }

    /**
     * @param ImageDefinitionInterface $imageDefinition
     * @param int $width
     * @param int $height
     * @return bool
     */
    private function checkValidSize(ImageDefinitionInterface $imageDefinition, int $width, int $height): bool
    {
        $state = false;

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
}
