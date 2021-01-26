<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Command\Media;

use Cocur\Slugify\Slugify;
use Ixocreate\Admin\Entity\User;
use Ixocreate\CommandBus\Command\AbstractCommand;
use Ixocreate\Filesystem\FilesystemInterface;
use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\Config\MediaPaths;
use Ixocreate\Media\CreateHandler\MediaCreateHandlerInterface;
use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\Exception\FileDuplicateException;
use Ixocreate\Media\Exception\FileSizeException;
use Ixocreate\Media\Exception\FileTypeNotSupportedException;
use Ixocreate\Media\Handler\MediaHandlerInterface;
use Ixocreate\Media\Handler\MediaHandlerSubManager;
use Ixocreate\Media\Repository\MediaRepository;

class CreateCommand extends AbstractCommand
{
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
     * @param MediaRepository $mediaRepository
     * @param MediaHandlerSubManager $mediaHandlerSubManager
     * @param MediaConfig $mediaConfig
     * @param FilesystemInterface $media
     */
    public function __construct(
        MediaRepository $mediaRepository,
        MediaHandlerSubManager $mediaHandlerSubManager,
        MediaConfig $mediaConfig,
        FilesystemInterface $media
    ) {
        $this->mediaRepository = $mediaRepository;
        $this->mediaHandlerSubManager = $mediaHandlerSubManager;
        $this->mediaConfig = $mediaConfig;
        $this->filesystem = $media;
    }

    /**
     * @param MediaCreateHandlerInterface $mediaCreateHandler
     * @return $this
     */
    public function withMediaCreateHandler(MediaCreateHandlerInterface $mediaCreateHandler): CreateCommand
    {
        $command = clone $this;
        $command->mediaCreateHandler = $mediaCreateHandler;
        return $command;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function withCreatedUser(User $user): CreateCommand
    {
        $command = clone $this;
        $command->createdUser = $user;
        return $command;
    }

    /**
     * @param bool $checkForDuplicates
     * @return $this
     */
    public function withCheckForDuplicates(bool $checkForDuplicates): CreateCommand
    {
        $command = clone $this;
        $command->checkForDuplicates = $checkForDuplicates;
        return $command;
    }

    /**
     * @param bool $publicStatus
     * @return $this
     */
    public function withPublicStatus(bool $publicStatus): CreateCommand
    {
        $command = clone $this;
        $command->publicStatus = $publicStatus;
        return $command;
    }

    /**
     * @param int $fileSizeLimit
     * @return $this
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
            /** @var Media $media */
            $media = $this->mediaRepository->findOneBy(['hash' => $this->fileHash]);
            $media = $media->with('updatedAt', new \DateTime());
            $this->mediaRepository->save($media);

            $this->uuid = (string)$media->id();
            return true;
            //throw new FileDuplicateException('File has already been uploaded');
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

        if ($this->createdUser !== null) {
            $media = $media->with('createdBy', $this->createdUser->id());
        }

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
            $basePath = \implode('/', \mb_str_split(\bin2hex(\random_bytes(3)), 2)) . '/';
            $exists = $this->filesystem->has($mediaPath . $basePath);
            if (\mb_strpos($basePath, 'ad') !== false) {
                $exists = false;
            }
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
