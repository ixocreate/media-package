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

namespace KiwiSuite\Media\Action;

use Firebase\JWT\JWT;
use KiwiSuite\Admin\Response\ApiErrorResponse;
use KiwiSuite\ApplicationHttp\ErrorHandling\Response\NotFoundHandler;
use KiwiSuite\Media\Entity\Media;
use KiwiSuite\Media\Repository\MediaRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;

final class StreamAction implements MiddlewareInterface
{
    /**
     * @var MediaRepository
     */
    private $mediaRepository;
    /**
     * @var NotFoundHandler
     */
    private $notFoundHandler;

    /**
     * UploadAction constructor.
     *
     * @param MediaRepository $mediaRepository
     * @param NotFoundHandler $notFoundHandler
     */
    public function __construct(
        MediaRepository $mediaRepository,
        NotFoundHandler $notFoundHandler
    ) {
        $this->mediaRepository = $mediaRepository;
        $this->notFoundHandler = $notFoundHandler;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $request->getAttribute('token');

        if (empty($token)) {
            return new ApiErrorResponse("bad_request");
        }

        /**
         * TODO: get secret from config
         */
        try {
            $jwt = JWT::decode($token, 'secret', ['HS512']);
        } catch (\Exception $e) {
            /**
             * TODO: do we want not founds or error response
             */
            return $this->notFoundHandler->process($request, $handler);
            // return new ApiErrorResponse("*", "invalid_token", 401);
        }

        /** @var Media $media */
        $media = $this->mediaRepository->find($jwt->data->mediaId);

        $storagePath = $media->publicStatus() ? 'data/media/' : 'data/media_private/';

        /**
         * make it work with delegator outputs
         */
        if (!empty($jwt->data->imageDefinition)) {
            $storagePath .= 'img/' . $jwt->data->imageDefinition . '/';
        }
        $filePath = $storagePath . $media->basePath() . $media->filename();

        /**
         * stream file
         */
        return (new Response())
            ->withHeader('Content-Type', $media->mimeType())
            ->withHeader('Content-Length', (string)$media->size())
            ->withHeader('Content-Disposition', 'inline; filename=' . $media->filename())
            ->withBody(new Stream($filePath));
    }
}
