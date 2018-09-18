<?php
declare(strict_types=1);

namespace KiwiSuite\Media\Action\Media;

use KiwiSuite\Admin\Response\ApiErrorResponse;
use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\Entity\Type\Type;
use KiwiSuite\Media\Delegator\Delegators\Image;
use KiwiSuite\Media\Entity\Media;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionInterface;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionSubManager;
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


    public function __construct(Uri $uri, Image $imageDelegator, ImageDefinitionSubManager $imageDefinitionSubManager)
    {
        $this->uri = $uri;
        $this->imageDelegator = $imageDelegator;
        $this->imageDefinitionSubManager = $imageDefinitionSubManager;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var MediaType $media */
        $media = Type::create($request->getAttribute("id"), MediaType::class);
        if (empty($media->value())) {
            return new ApiErrorResponse('given_media_Id_does_not_exist');
        }


        $isCropable = false;
        $result = [
            'media' => $media->jsonSerialize(),
            'isCropable' => $isCropable,
        ];

        $definitions = [];

        if ($this->imageDelegator->isResponsible($media->value())) {
            foreach($this->imageDefinitionSubManager->getServices() as $name) {
                $imageDefinition = $this->imageDefinitionSubManager->get($name);
                $isCropable = $this->checkCropable($media->value(),$imageDefinition);
                $definitions[] = ['name' => $imageDefinition::serviceName(), 'isCropable' => $isCropable];
            }
            $result = [
                'media' => $media->jsonSerialize(),
                'definitions' => $definitions
            ];
        }


//        if ($isCropable === true) {
//
//        }


        return new ApiSuccessResponse($result);
    }

    private function checkCropable(Media $media, ImageDefinitionInterface $imageDefinition): bool
    {
        $state = false;

        $size = \getimagesize(getcwd(). '/data/media/' . $media->basePath() . $media->filename());

        if ($size[0] = $size[1]) {
            if ($size[0] >= $imageDefinition->width() && $size[0] >= $imageDefinition->height()) {
                $state = true;
            }
        }

        if ($size[0] > $size[1]) {
            if ($size[0] >= $imageDefinition->width()) {
                $state = true;
            }
        }

        if ($size[1] > $size[0]) {
            if ($size[1] >= $imageDefinition->height()) {
                $state = true;
            }
        }

        return $state;
    }

}