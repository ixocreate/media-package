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

use KiwiSuite\Media\Config\MediaConfig;
use KiwiSuite\Media\Config\MediaConfigurator;
use KiwiSuite\Media\Delegator\DelegatorInterface;
use KiwiSuite\Media\Entity\Media;
use KiwiSuite\Media\Repository\MediaRepository;
use Cocur\Slugify\Slugify;
use KiwiSuite\Admin\Response\ApiErrorResponse;
use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\CommandBus\CommandBus;
use KiwiSuite\Media\Delegator\DelegatorSubManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\UploadedFile;

final class UploadAction implements MiddlewareInterface
{
    /**
     * @var MediaRepository
     */
    private $mediaRepository;

    /**
     * @var DelegatorSubManager
     */
    private $delegatorSubManager;

    /**
     * @var MediaConfig
     */
    private $mediaConfig;

    /**
     * UploadAction constructor.
     * @param MediaRepository $mediaRepository
     * @param DelegatorSubManager $delegatorSubManager
     */
    public function __construct(MediaRepository $mediaRepository, DelegatorSubManager $delegatorSubManager, MediaConfig $mediaConfig)
    {
        $this->mediaRepository = $mediaRepository;
        $this->delegatorSubManager = $delegatorSubManager;
        $this->mediaConfig = $mediaConfig;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!\array_key_exists('file', $request->getUploadedFiles())) {
            return new ApiErrorResponse("invalid_file");
        }

        $upload = $request->getUploadedFiles()['file'];

        if (!($upload instanceof UploadedFile)) {
            return new ApiErrorResponse("invalid_file");
        }

        if (!($this->checkWhitelist($upload))) {
            return new ApiErrorResponse("file_type_not_supported");
        }

        if ($this->checkDuplicate($upload)) {
            return new ApiErrorResponse('file_already_exists');
        }

        $media = $this->prepareMedia($upload);

        foreach ($this->delegatorSubManager->getServiceManagerConfig()->getNamedServices() as $name => $delegator) {
            /** @var DelegatorInterface $delegator */
            $delegator = $this->delegatorSubManager->get($delegator);
            if (!$delegator->isResponsible($media)) {
                continue;
            }
            $delegator->process($media);
        }

        $this->mediaRepository->save($media);

        return new ApiSuccessResponse();
    }

    /**
     * @param UploadedFile $upload
     * @return Media
     * @throws \Exception
     */
    private function prepareMedia(UploadedFile $upload): Media
    {
        $basePath = $this->createDir();
        $filenameParts = \pathinfo($upload->getClientFilename());
        $slugify = new Slugify();
        $filename = $slugify->slugify($filenameParts['filename']) . '.' . $filenameParts['extension'];

        $upload->moveTo('data/media/' . $basePath . $filename);

        $finfo = \finfo_open(FILEINFO_MIME_TYPE);

        $media = new Media([
            'id' => Uuid::uuid4(),
            'basePath' => $basePath,
            'filename' => $filename,
            'mimeType' => finfo_file($finfo, 'data/media/' . $basePath . $filename),
            'size' => sprintf('%u', filesize('data/media/' . $basePath . $filename)),
            'publicStatus' => false,
            'hash' => hash_file('sha256','data/media/' . $basePath . $filename),
            'createdAt' => new \DateTimeImmutable(),
            'updatedAt' => new \DateTimeImmutable(),
        ]);
        return $media;
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function createDir(): string
    {
        do {
            $basePath = \implode('/', str_split(bin2hex(random_bytes(3)), 2)) . '/';
            $exists = \is_dir('data/media/' . $basePath);
        } while ($exists === true);

        \mkdir('data/media/' . $basePath, 0777, true);

        return $basePath;
    }

    /**
     * @param $upload
     * @return bool
     */
    private function checkDuplicate(UploadedFile $upload): bool
    {
        $count =
            $this->mediaRepository->count(['hash' => hash_file('sha256',$upload->getStream()->getMetadata()['uri'])]);

        return ($count > 0);
    }

    /**
     * @param $upload
     * @return bool
     */
    private function checkWhitelist(UploadedFile $upload): bool
    {
        $supported = false;
        $filenameParts = \pathinfo($upload->getClientFilename());
        foreach ($this->mediaConfig->whitelist() as $list) {
            if (array_key_exists($filenameParts['extension'], $list) || in_array($upload->getClientMediaType(), $list)) {
                $supported = true;
            }
        }
        return $supported;
    }
}
