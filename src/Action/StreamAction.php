<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Action;

use Firebase\JWT\JWT;
use Ixocreate\Admin\Config\AdminConfig;
use Ixocreate\Admin\Response\ApiErrorResponse;
use Ixocreate\Application\Http\ErrorHandling\Response\NotFoundHandler;
use Ixocreate\Filesystem\FilesystemManager;
use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\Config\MediaPaths;
use Ixocreate\Media\Repository\MediaRepository;
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
     * @var AdminConfig
     */
    private $adminConfig;

    /**
     * @var FilesystemManager
     */
    private $filesystemManager;

    /**
     * UploadAction constructor.
     *
     * @param MediaRepository $mediaRepository
     * @param NotFoundHandler $notFoundHandler
     * @param FilesystemManager $filesystemManager
     * @param AdminConfig $adminConfig
     */
    public function __construct(
        MediaRepository $mediaRepository,
        NotFoundHandler $notFoundHandler,
        FilesystemManager $filesystemManager,
        AdminConfig $adminConfig
    ) {
        $this->mediaRepository = $mediaRepository;
        $this->notFoundHandler = $notFoundHandler;
        $this->adminConfig = $adminConfig;
        $this->filesystemManager = $filesystemManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @throws \Exception
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $request->getAttribute('token');

        if (empty($token)) {
            return new ApiErrorResponse("bad_request");
        }

        if (empty($this->adminConfig->secret())) {
            return new ApiErrorResponse('Secret is not set in AdminConfig');
        }

        if (!$this->filesystemManager->has('media')) {
            return new ApiErrorResponse('Storage Config not set');
        }

        $filesystem = $this->filesystemManager->get('media');

        try {
            $jwt = JWT::decode($token, $this->adminConfig->secret(), ['HS512']);
        } catch (\Exception $e) {
            /**
             * TODO: do we want not founds or error response
             */
            return $this->notFoundHandler->process($request, $handler);
        }

        /** @var Media $media */
        $media = $this->mediaRepository->find($jwt->data->mediaId);

        $mediaPath = $media->publicStatus() ? MediaPaths::PUBLIC_PATH : MediaPaths::PRIVATE_PATH;
        $storagePath = null;
        /**
         * make it work with delegator outputs
         */
        if (!empty($jwt->data->imageDefinition)) {
            $storagePath = MediaPaths::IMAGE_DEFINITION_PATH . $jwt->data->imageDefinition . '/';
        }
        $fileSize = $filesystem->getSize($mediaPath . $storagePath . $media->basePath() . $media->filename());

        $fileStream = $filesystem->readStream($mediaPath . $storagePath . $media->basePath() . $media->filename());

        /**
         * stream file
         */
        return (new Response())
            ->withHeader('Content-Type', $media->mimeType())
            ->withHeader('Content-Length', (string)$fileSize)
            ->withHeader('Content-Disposition', 'inline; filename=' . $media->filename())
            ->withBody(new Stream($fileStream));
    }
}
