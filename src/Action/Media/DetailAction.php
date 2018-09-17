<?php
declare(strict_types=1);

namespace KiwiSuite\Media\Action\Media;

use KiwiSuite\Admin\Response\ApiErrorResponse;
use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\Entity\Type\Type;
use KiwiSuite\Media\Delegator\Delegators\Image;
use KiwiSuite\Media\Repository\MediaRepository;
use KiwiSuite\Media\Type\MediaType;
use KiwiSuite\Media\Uri\Uri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class DetailAction implements MiddlewareInterface
{
    private $mediaRepository;
    /**
     * @var Uri
     */
    private $uri;
    /**
     * @var Image
     */
    private $imageDelegator;

    public function __construct(MediaRepository $mediaRepository, Uri $uri, Image $imageDelegator)
    {
        $this->mediaRepository = $mediaRepository;
        $this->uri = $uri;
        $this->imageDelegator = $imageDelegator;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var MediaType $media */
        $media = Type::create($request->getAttribute("id"), MediaType::class);

        if (empty($media->value())) {
            return new ApiErrorResponse('given media Id does not exist');
        }

        if (!empty($media->value()->deletedAt())) {
            return new ApiErrorResponse('given media Id does not exist');
        }

        $isCropable = $this->imageDelegator->isResponsible($media->value());
        $result = [
            'media' => $media->jsonSerialize(),
            'isCropable' => $isCropable,
        ];

        if ($isCropable === true) {

        }

        return new ApiSuccessResponse($result);
    }

}