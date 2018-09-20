<?php
declare(strict_types=1);

namespace KiwiSuite\Media\Action\Media;

use KiwiSuite\Admin\Response\ApiErrorResponse;
use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\Entity\Type\Type;
use KiwiSuite\Media\Delegator\Delegators\Image;
use KiwiSuite\Media\Entity\Media;
use KiwiSuite\Media\Entity\MediaCrop;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionInterface;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionSubManager;
use KiwiSuite\Media\Repository\MediaCropRepository;
use KiwiSuite\Media\Type\MediaType;
use KiwiSuite\Media\Uri\Uri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class DetailAction implements MiddlewareInterface
{
    /**
     * @var Uri
     */
    private $uri;

    /**
     * @var Image
     */
    private $imageDelegator;

    /**
     * @var ImageDefinitionSubManager
     */
    private $imageDefinitionSubManager;

    /**
     * @var MediaCropRepository
     */
    private $mediaCropRepository;

    /**
     * DetailAction constructor.
     * @param Uri $uri
     * @param Image $imageDelegator
     * @param ImageDefinitionSubManager $imageDefinitionSubManager
     * @param MediaCropRepository $mediaCropRepository
     */
    public function __construct(
        Uri $uri,
        Image $imageDelegator,
        ImageDefinitionSubManager $imageDefinitionSubManager,
        MediaCropRepository $mediaCropRepository
    )
    {
        $this->uri = $uri;
        $this->imageDelegator = $imageDelegator;
        $this->imageDefinitionSubManager = $imageDefinitionSubManager;
        $this->mediaCropRepository = $mediaCropRepository;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var MediaType $media */
        $media = Type::create($request->getAttribute("id"), MediaType::class);
        if (empty($media->value())) {
            return new ApiErrorResponse('given_media_Id_does_not_exist');
        }


        $isCropable = $this->imageDelegator->isResponsible($media->value());

        $result = [
            'media' => $media->jsonSerialize(),
            'isCropable' => $isCropable,
        ];

        $media = $media->value();

        $mediaCropArray = $this->mediaCropRepository->findBy(['mediaId' => ($media->id())]);

        if ($isCropable) {
            $definitions = $this->determineDefinitions($media,$mediaCropArray);
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

        $size = \getimagesize(\getcwd(). '/data/media/' . $media->basePath() . $media->filename());
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
//        if ($width > $height) {
//            if ($width >= $imageDefinition->width()) {
//                $state = true;
//            }
//        }
//
//        if ($height > $width) {
//            if ($height >= $imageDefinition->height()) {
//                $state = true;
//            }
//        }

        return $state;
    }

    /**
     * @param Media $media
     * @param array $mediaCropArray
     * @return array
     */
    private function determineDefinitions(Media $media, array $mediaCropArray)
    {
        $definitions = [];

        foreach($this->imageDefinitionSubManager->getServices() as $key => $name) {
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