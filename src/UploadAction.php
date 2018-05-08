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

namespace KiwiSuite\Media;

use KiwiSuite\Media\Entity\Media;
use KiwiSuite\Media\Repository\MediaRepository;
use Cocur\Slugify\Slugify;
use KiwiSuite\Admin\Response\ApiErrorResponse;
use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\CommandBus\CommandBus;
use KiwiSuite\Media\Delegator\DelegatorMapping;
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
     * @var CommandBus
     */
    private $commandBus;
    /**
     * @var MediaRepository
     */
    private $mediaRepository;

    /**
     * @var DelegatorMapping
     */
    private $delegatorMapping;

    /**
     * @var DelegatorSubManager
     */
    private $delegatorSubManager;

    /**
     * @var int
     */
    private $countDelegators;

    /**
     * UploadAction constructor.
     * @param CommandBus $commandBus
     * @param MediaRepository $mediaRepository
     * @param DelegatorMapping $delegators
     * @param DelegatorSubManager $delegatorSubManager
     */
    public function __construct(CommandBus $commandBus, MediaRepository $mediaRepository, DelegatorMapping $delegators, DelegatorSubManager $delegatorSubManager)
    {
        $this->commandBus = $commandBus;
        $this->mediaRepository = $mediaRepository;
        $this->delegatorMapping = $delegators;
        $this->delegatorSubManager = $delegatorSubManager;
        $this->countDelegators = count($this->delegatorMapping->getMapping());
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

        do {
            $basePath = \implode('/', str_split(bin2hex(random_bytes(3)), 2)) . '/';
            $exists = is_dir('data/media/' . $basePath);
        } while ($exists === true);

        mkdir('data/media/' . $basePath, 0777, true);
        $filenameParts = pathinfo($upload->getClientFilename());
        $slugify = new Slugify();
        $filename = $slugify->slugify($filenameParts['filename']) . '.' . $filenameParts['extension'];

        $upload->moveTo('data/media/' . $basePath . $filename);

        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        $media = new Media([
            'id' => Uuid::uuid4(),
            'basePath' => $basePath,
            'filename' => $filename,
            'mimeType' => finfo_file($finfo, 'data/media/' . $basePath . $filename),
            'size' => sprintf('%u', filesize('data/media/' . $basePath . $filename)),
            'createdAt' => new \DateTimeImmutable(),
        ]);

        $notResponsible = 0;
        foreach ($this->delegatorMapping->getMapping() as $delegator) {
            $delegator = $this->delegatorSubManager->get($delegator);
            if ($delegator->responsible($media) === false) {
                $notResponsible++;
            }
        }

        if ($notResponsible === $this->countDelegators) {
            return new ApiErrorResponse('File-Type not supported');
        }

        $this->mediaRepository->save($media);

        return new ApiSuccessResponse();
    }
}