<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Command\Media;

use Cocur\Slugify\Slugify;
use Ixocreate\Admin\Entity\User;
use Ixocreate\CommandBus\Command\AbstractCommand;
use Ixocreate\Filesystem\FilesystemInterface;
use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\Entity\MediaCreated;
use Ixocreate\Media\Exception\FileDuplicateException;
use Ixocreate\Media\Exception\FileSizeException;
use Ixocreate\Media\Exception\FileTypeNotSupportedException;
use Ixocreate\Media\MediaHandlerInterface;
use Ixocreate\Media\Handler\MediaHandlerSubManager;
use Ixocreate\Media\MediaCreateHandlerInterface;
use Ixocreate\Media\MediaPaths;
use Ixocreate\Media\Repository\MediaCreatedRepository;
use Ixocreate\Media\Repository\MediaRepository;

class CreateCommand extends AbstractCommand
{
    /**
     * @var MediaCreatedRepository
     */
    private $mediaCreatedRepository;

    /**
     * @var MediaRepository
     */
    private $mediaRepository;

    /**
     * @var MediaHandlerSubManager
     */
    private $mediaHandlerSubManager;

    /**
     * @var MediaConfig
     */
    private $mediaConfig;

    /**
     * @var MediaCreateHandlerInterface
     */
    private $mediaCreateHandler;

    /**
     * @var User
     */
    private $createdUser;

    /**
     * @var bool
     */
    private $checkForDuplicates = true;

    /**
     * @var bool
     */
    private $publicStatus = true;

    /**
     * @var string
     */
    private $fileHash;

    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var null | int
     */
    private $fileSizeLimit = null;

    /**
     * CreateCommand constructor.
     *
     * @param MediaCreatedRepository $mediaCreatedRepository
     * @param MediaRepository $mediaRepository
     * @param MediaHandlerSubManager $mediaHandlerSubManager
     * @param MediaConfig $mediaConfig
     */
    public function __construct(
        MediaCreatedRepository $mediaCreatedRepository,
        MediaRepository $mediaRepository,
        MediaHandlerSubManager $mediaHandlerSubManager,
        MediaConfig $mediaConfig
    ) {
        $this->mediaCreatedRepository = $mediaCreatedRepository;
        $this->mediaRepository = $mediaRepository;
        $this->mediaHandlerSubManager = $mediaHandlerSubManager;
        $this->mediaConfig = $mediaConfig;
    }

    /**
     * @param MediaCreateHandlerInterface $mediaCreateHandler
     * @return CreateCommand
     */
    public function withMediaCreateHandler(MediaCreateHandlerInterface $mediaCreateHandler): CreateCommand
    {
        $command = clone $this;
        $command->mediaCreateHandler = $mediaCreateHandler;
        return $command;
    }

    /**
     * @param User $user
     * @return CreateCommand
     */
    public function withCreatedUser(User $user): CreateCommand
    {
        $command = clone $this;
        $command->createdUser = $user;
        return $command;
    }

    /**
     * @param bool $checkForDuplicates
     * @return CreateCommand
     */
    public function withCheckForDuplicates(bool $checkForDuplicates): CreateCommand
    {
        $command = clone $this;
        $command->checkForDuplicates = $checkForDuplicates;
        return $command;
    }

    /**
     * @param bool $publicStatus
     * @return CreateCommand
     */
    public function withPublicStatus(bool $publicStatus): CreateCommand
    {
        $command = clone $this;
        $command->publicStatus = $publicStatus;
        return $command;
    }

    /**
     * @param FilesystemInterface $filesystem
     * @return CreateCommand
     */
    public function withFilesystem(FilesystemInterface $filesystem): CreateCommand
    {
        $command = clone $this;
        $command->filesystem = $filesystem;
        return $command;
    }

    /**
     * @param int $fileSizeLimit
     * @return CreateCommand
     */
    public function withFileSizeLimit(int $fileSizeLimit): CreateCommand
    {
        $command = clone $this;
        $command->fileSizeLimit = $fileSizeLimit;
        return $command;
    }

    /**
     * @throws \Exception
     * @return bool
     */
    public function execute(): bool
    {
        if (!($this->checkWhitelist($this->mediaCreateHandler->mimeType()))) {
            throw new FileTypeNotSupportedException('Mime Type not supported');
        }

        if ($this->fileSizeLimit !== null && $this->mediaCreateHandler->fileSize() > $this->fileSizeLimit) {
            throw new FileSizeException('Allowed File Size exceeded');
        }

        $this->fileHash = $this->mediaCreateHandler->fileHash();

        if ($this->checkForDuplicates && $this->checkDuplicate($this->fileHash)) {
            throw new FileDuplicateException('File has already been uploaded');
        }

        $media = $this->prepareMedia();

        $media = $this->mediaRepository->save($media);

        foreach ($this->mediaHandlerSubManager->getServiceManagerConfig()->getNamedServices() as $name => $mediaHandlerClassName) {
            /** @var MediaHandlerInterface $$handler */
            $handler = $this->mediaHandlerSubManager->get($mediaHandlerClassName);
            if (!$handler->isResponsible($media)) {
                continue;
            }
            $handler->process($media, $this->filesystem);
        }

        if ($this->createdUser !== null) {
            $mediaCreated = new MediaCreated([
                'mediaId' => $media->id(),
                'createdBy' => $this->createdUser->id(),
            ]);
            $this->mediaCreatedRepository->save($mediaCreated);
        }
        return true;
    }

    /**
     * @throws \Exception
     * @return Media
     */
    private function prepareMedia(): Media
    {
        $mediaPath = $this->publicStatus ? MediaPaths::PUBLIC_PATH : MediaPaths::PRIVATE_PATH;
        $basePath = $this->createDir($mediaPath);
        $filenameParts = \pathinfo($this->mediaCreateHandler->filename());
        $slugify = new Slugify();
        $filename = $slugify->slugify($filenameParts['filename']) . '.' . $filenameParts['extension'];
        $destination = $mediaPath . $basePath . $filename;

        // Save File to Drive
        $this->mediaCreateHandler->write($this->filesystem, $destination);

        $media = new Media([
            'id' => $this->uuid(),
            'basePath' => $basePath,
            'filename' => $filename,
            'fileSize' => $this->mediaCreateHandler->fileSize(),
            'mimeType' => $this->mediaCreateHandler->mimeType(),
            'publicStatus' => $this->publicStatus,
            'hash' => $this->fileHash,
            'createdAt' => new \DateTimeImmutable(),
            'updatedAt' => new \DateTimeImmutable(),
        ]);

        return $media;
    }

    /**
     * @param string $mediaPath
     * @throws \Exception
     * @return string
     */
    private function createDir(string $mediaPath): string
    {
        do {
            $basePath = \implode('/', \str_split(\bin2hex(\random_bytes(3)), 2)) . '/';

            $folders = \explode('/', $basePath);

            if (\in_array('ad', $folders)) {
                continue;
            }

            $exists = $this->filesystem->has($mediaPath . $basePath);
        } while ($exists === true);

        $this->filesystem->createDir($mediaPath . $basePath);

        return $basePath;
    }

    /**
     * @param string $hash
     * @return bool
     */
    private function checkDuplicate(string $hash): bool
    {
        return ($this->mediaRepository->count(['hash' => $hash])) > 0;
    }

    /**
     * @param string $mimeType
     * @return bool
     */
    private function checkWhitelist(string $mimeType): bool
    {
        return \in_array($mimeType, $this->mediaConfig->whitelist());
    }

    public static function serviceName(): string
    {
        return 'media-media-create';
    }
}
