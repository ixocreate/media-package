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
use Ixocreate\Media\DelegatorInterface;
use Ixocreate\Media\MediaCreateHandlerInterface;
use Ixocreate\Filesystem\Storage\StorageSubManager;
use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\Delegator\DelegatorSubManager;
use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\Entity\MediaCreated;
use Ixocreate\Media\Exception\FileDuplicateException;
use Ixocreate\Media\Exception\FileTypeNotSupportedException;
use Ixocreate\Media\Exception\InvalidConfigException;
use Ixocreate\Media\MediaPaths;
use Ixocreate\Media\Repository\MediaCreatedRepository;
use Ixocreate\Media\Repository\MediaRepository;
use League\Flysystem\FilesystemInterface;

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
     * @var DelegatorSubManager
     */
    private $delegatorSubManager;

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
     * @var StorageSubManager
     */
    private $storageSubManager;

    /**
     * @var FilesystemInterface
     */
    private $storage;

    /**
     * @var string
     */
    private $fileHash;

    /**
     * CreateCommand constructor.
     *
     * @param MediaCreatedRepository $mediaCreatedRepository
     * @param MediaRepository $mediaRepository
     * @param DelegatorSubManager $delegatorSubManager
     * @param MediaConfig $mediaConfig
     * @param StorageSubManager $storageSubManager
     */
    public function __construct(
        MediaCreatedRepository $mediaCreatedRepository,
        MediaRepository $mediaRepository,
        DelegatorSubManager $delegatorSubManager,
        MediaConfig $mediaConfig,
        StorageSubManager $storageSubManager
    ) {
        $this->mediaCreatedRepository = $mediaCreatedRepository;
        $this->mediaRepository = $mediaRepository;
        $this->delegatorSubManager = $delegatorSubManager;
        $this->mediaConfig = $mediaConfig;
        $this->storageSubManager = $storageSubManager;
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
     * @throws \Exception
     * @return bool
     */
    public function execute(): bool
    {
        if (!$this->storageSubManager->has('media')) {
            throw new InvalidConfigException('Storage Config not set');
        }

        $this->storage = $this->storageSubManager->get('media');

        if (!($this->checkWhitelist($this->mediaCreateHandler->mimeType()))) {
            throw new FileTypeNotSupportedException('Mime Type not supported');
        }

        $this->fileHash = $this->mediaCreateHandler->fileHash();

        if ($this->checkForDuplicates && $this->checkDuplicate($this->fileHash)) {
            throw new FileDuplicateException('File has already been uploaded');
        }

        $media = $this->prepareMedia();

        $this->mediaRepository->save($media);

        foreach ($this->delegatorSubManager->getServiceManagerConfig()->getNamedServices() as $name => $delegatorClassName) {
            /** @var DelegatorInterface $delegator */
            $delegator = $this->delegatorSubManager->get($delegatorClassName);
            if (!$delegator->isResponsible($media)) {
                continue;
            }
            $delegator->process($media);
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

        $this->mediaCreateHandler->move($this->storage, $destination);

        $media = new Media([
            'id' => $this->uuid(),
            'basePath' => $basePath,
            'filename' => $filename,
            'mimeType' => $this->mediaCreateHandler->mimeType(),
            'size' => $this->mediaCreateHandler->fileSize(),
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

            $exists = $this->storage->has($mediaPath . $basePath);
        } while ($exists === true);

        $this->storage->createDir($mediaPath . $basePath);

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
