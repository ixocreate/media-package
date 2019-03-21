<?php
/**
 * @see https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Command;

use Ixocreate\CommandBus\Command\AbstractCommand;
use Ixocreate\Contract\Media\DelegatorInterface;
use Ixocreate\Filesystem\Storage\StorageSubManager;
use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\Delegator\DelegatorSubManager;
use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\Exception\InvalidConfigException;
use Ixocreate\Media\MediaPaths;
use Ixocreate\Media\Repository\MediaRepository;
use League\Flysystem\FilesystemInterface;

class ChangePublicStatusCommand extends AbstractCommand
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
     * @var Media
     */
    private $media;

    /**
     * @var bool
     */
    private $publicStatus;

    /**
     * @var StorageSubManager
     */
    private $storageSubManager;

    /**
     * @var FilesystemInterface
     */
    private $storage;

    /**
     * CreateCommand constructor.
     *
     * @param MediaRepository $mediaRepository
     * @param DelegatorSubManager $delegatorSubManager
     * @param MediaConfig $mediaConfig
     * @param StorageSubManager $storageSubManager
     */
    public function __construct(
        MediaRepository $mediaRepository,
        DelegatorSubManager $delegatorSubManager,
        MediaConfig $mediaConfig,
        StorageSubManager $storageSubManager
    )
    {
        $this->mediaRepository = $mediaRepository;
        $this->delegatorSubManager = $delegatorSubManager;
        $this->mediaConfig = $mediaConfig;
        $this->storageSubManager = $storageSubManager;
    }

    /**
     * @param Media $media
     * @return ChangePublicStatusCommand
     */
    public function withMedia(Media $media): ChangePublicStatusCommand
    {
        $command = clone $this;
        $command->media = $media;
        return $command;
    }

    /**
     * @param bool $publicStatus
     * @return ChangePublicStatusCommand
     */
    public function withPublicStatus(bool $publicStatus): ChangePublicStatusCommand
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

        $desiredPublicStatus = $this->media->publicStatus();
        if ($this->publicStatus !== null) {
            $desiredPublicStatus = $this->publicStatus;
        }

        /**
         * check if already moved
         */
        $basePath = $this->media->basePath();
        $publicDirectory = MediaPaths::PUBLIC_PATH . $basePath;
        $privateDirectory = MediaPaths::PRIVATE_PATH . $basePath;
        if ($desiredPublicStatus && !$this->storage->has($publicDirectory)) {
            $this->moveMedia($this->media, MediaPaths::PRIVATE_PATH, MediaPaths::PUBLIC_PATH);
        } elseif (!$desiredPublicStatus && !$this->storage->has($privateDirectory)) {
            $this->moveMedia($this->media, MediaPaths::PUBLIC_PATH, MediaPaths::PRIVATE_PATH);
        }

        if ($this->media->publicStatus() !== $desiredPublicStatus) {
            $this->media = $this->media->with('publicStatus', $desiredPublicStatus);
            $this->media = $this->media->with('updatedAt', new \DateTime());
            $this->mediaRepository->save($this->media);
        }

        return true;
    }

    /**
     * @param Media $media
     * @param string $fromStoragePath
     * @param string $toStoragePath
     * @return Media
     * @throws \League\Flysystem\FileExistsException
     * @throws \League\Flysystem\FileNotFoundException
     */
    private function moveMedia(Media $media, string $fromStoragePath, string $toStoragePath): Media
    {
        /**
         * move source file
         */
        $this->storage->rename($fromStoragePath . $media->basePath(), $toStoragePath . $media->basePath());

        /**
         * move output files from delegators as well
         */
        foreach ($this->delegatorSubManager->getServiceManagerConfig()->getNamedServices() as $name => $delegatorClassName) {
            /** @var DelegatorInterface $delegator */
            $delegator = $this->delegatorSubManager->get($delegatorClassName);

            if (!$delegator->isResponsible($media)) {
                continue;
            }

            foreach ($delegator->directories() as $directory) {
                $this->storage->rename($fromStoragePath . $directory . $media->basePath(), $toStoragePath . $directory . $media->basePath());
            }
        }

        return $media;
    }

    /**
     * @return string
     */
    public static function serviceName(): string
    {
        return 'media-change-public-status';
    }
}
