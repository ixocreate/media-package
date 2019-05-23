<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Command\Media;

use DateTimeImmutable;
use Exception;
use Ixocreate\CommandBus\Command\AbstractCommand;
use Ixocreate\Filesystem\FilesystemInterface;
use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\MediaHandlerInterface;
use Ixocreate\Media\Handler\MediaHandlerSubManager;
use Ixocreate\Media\MediaInterface;
use Ixocreate\Media\MediaPaths;
use Ixocreate\Media\Repository\MediaRepository;

class UpdateCommand extends AbstractCommand
{
    /**
     * @var MediaRepository
     */
    private $mediaRepository;

    /**
     * @var MediaConfig
     */
    private $mediaConfig;

    /**
     * @var MediaHandlerSubManager
     */
    private $mediaHandlerSubManager;

    /**
     * @var Media
     */
    private $media;

    /**
     * @var bool
     */
    private $publicStatus;

    /**
     * @var string
     */
    private $newFilename;

    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * UpdateCommand constructor.
     *
     * @param MediaHandlerSubManager $mediaHandlerSubManager
     * @param MediaRepository $mediaRepository
     * @param MediaConfig $mediaConfig
     */
    public function __construct(
        MediaHandlerSubManager $mediaHandlerSubManager,
        MediaRepository $mediaRepository,
        MediaConfig $mediaConfig
    ) {
        $this->mediaRepository = $mediaRepository;
        $this->mediaConfig = $mediaConfig;
        $this->mediaHandlerSubManager = $mediaHandlerSubManager;
    }

    /**
     * @param Media $media
     * @return UpdateCommand
     */
    public function withMedia(Media $media): UpdateCommand
    {
        $command = clone $this;
        $command->media = $media;
        return $command;
    }

    /**
     * @param bool $publicStatus
     * @return UpdateCommand
     */
    public function withPublicStatus(bool $publicStatus): UpdateCommand
    {
        $command = clone $this;
        $command->publicStatus = $publicStatus;
        return $command;
    }

    /**
     * @param string $newFilename
     * @return UpdateCommand
     */
    public function withNewFilename(string $newFilename): UpdateCommand
    {
        $command = clone $this;
        $command->newFilename = $newFilename;
        return $command;
    }

    public function withFilesystem(FilesystemInterface $filesystem): UpdateCommand
    {
        $command = clone $this;
        $command->filesystem = $filesystem;
        return $command;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function execute(): bool
    {
        // Filename
        if ($this->newFilename !== null) {
            $this->filterNewFilename();
            /** @var string $newFilename */
            $newFilename = $this->newFilename;

            $fileInfo = \pathinfo($this->media->filename());

            if ($fileInfo['filename'] !== $newFilename) {
                $this->media = $this->renameFiles($this->media, $newFilename, $fileInfo);
                $this->media = $this->media->with('filename', $this->newFilename . '.' . $fileInfo['extension']);
            }
        }

        // PublicStatus
        if ($this->publicStatus !== null) {
            if ($this->mediaConfig->publicStatus()) {
                $desiredPublicStatus = $this->publicStatus;
                /**
                 * check if already moved
                 */
                $publicDirectory = MediaPaths::PUBLIC_PATH . $this->media->basePath() . $this->media->filename();
                $privateDirectory = MediaPaths::PRIVATE_PATH . $this->media->basePath() . $this->media->filename();
                if ($desiredPublicStatus && !$this->filesystem->has($publicDirectory)) {
                    $this->media = $this->moveMedia($this->media, MediaPaths::PRIVATE_PATH, MediaPaths::PUBLIC_PATH);
                } elseif (!$desiredPublicStatus && !$this->filesystem->has($privateDirectory)) {
                    $this->media = $this->moveMedia($this->media, MediaPaths::PUBLIC_PATH, MediaPaths::PRIVATE_PATH);
                }

                if ($this->media->publicStatus() !== $desiredPublicStatus) {
                    $this->media = $this->media->with('publicStatus', $desiredPublicStatus);
                }
            }
        }

        $this->media = $this->media->with('updatedAt', new DateTimeImmutable());

        $this->mediaRepository->save($this->media);

        return true;
    }

    /**
     * @param MediaInterface $media
     * @param string $newFilename
     * @param array $fileInfo
     * @return MediaInterface
     */
    private function renameFiles(MediaInterface $media, string $newFilename, array $fileInfo): MediaInterface
    {
        $mediaPath = $media->publicStatus() ? MediaPaths::PUBLIC_PATH : MediaPaths::PRIVATE_PATH;
        foreach ($this->mediaHandlerSubManager->getServices() as $mediaHandlerClassName) {
            /** @var MediaHandlerInterface $mediaHandler */
            $mediaHandler = $this->mediaHandlerSubManager->get($mediaHandlerClassName);
            if ($mediaHandler->isResponsible($media)) {
                foreach ($mediaHandler->directories() as $directory) {
                    $this->filesystem->rename(
                        $mediaPath . $directory . $media->basePath() . $media->filename(),
                        $mediaPath . $directory . $media->basePath() . $newFilename . '.' . $fileInfo['extension']
                    );
                }
            }
        }
        $this->filesystem->rename(
            $mediaPath . $media->basePath() . $media->filename(),
            $mediaPath . $media->basePath() . $newFilename . '.' . $fileInfo['extension']
        );

        return $media;
    }

    /**
     * @param MediaInterface $media
     * @param string $fromMediaPath
     * @param string $toMediaPath
     * @return MediaInterface
     */
    private function moveMedia(MediaInterface $media, string $fromMediaPath, string $toMediaPath): MediaInterface
    {
        /**
         * move source file
         */
        $this->filesystem->rename(
            $fromMediaPath . $media->basePath() . $media->filename(),
            $toMediaPath . $media->basePath() . $media->filename()
        );

        /**
         * move output files from mediaHandlers as well
         */
        foreach ($this->mediaHandlerSubManager->getServices() as $mediaHandlerClassName) {
            /** @var MediaHandlerInterface $mediaHandler */
            $mediaHandler = $this->mediaHandlerSubManager->get($mediaHandlerClassName);

            if (!$mediaHandler->isResponsible($media)) {
                continue;
            }

            foreach ($mediaHandler->directories() as $directory) {
                $this->filesystem->rename(
                    $fromMediaPath . $directory . $media->basePath() . $media->filename(),
                    $toMediaPath . $directory . $media->basePath() . $media->filename()
                );
            }
        }

        return $media;
    }

    private function filterNewFilename(): void
    {
        // remove whitespace
        $newFilename = \trim($this->newFilename);
        // remove Extension
        $newFilename = \pathinfo($newFilename, PATHINFO_FILENAME);
        // remove all special Characters except "-_ /."
        $newFilename = \preg_replace("/([^A-Za-z0-9ÖöÄäÜü\/\-_\.])/", "", $newFilename);

        $this->newFilename = $newFilename;
    }

    /**
     * @return string
     */
    public static function serviceName(): string
    {
        return 'media-media-update';
    }
}
