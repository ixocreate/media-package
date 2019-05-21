<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Command\Media;

use Ixocreate\CommandBus\Command\AbstractCommand;
use Ixocreate\Filesystem\FilesystemInterface;
use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\Handler\MediaHandlerSubManager;
use Ixocreate\Media\MediaHandlerInterface;
use Ixocreate\Media\MediaPaths;
use Ixocreate\Media\Repository\MediaRepository;

class DeleteCommand extends AbstractCommand
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
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * CreateCommand constructor.
     *
     * @param MediaRepository $mediaRepository
     * @param MediaHandlerSubManager $mediaHandlerSubManager
     */
    public function __construct(
        MediaRepository $mediaRepository,
        MediaHandlerSubManager $mediaHandlerSubManager
    )
    {
        $this->mediaRepository = $mediaRepository;
        $this->mediaHandlerSubManager = $mediaHandlerSubManager;
    }

    /**
     * @param FilesystemInterface $filesystem
     * @return DeleteCommand
     */
    public function withFilesystem(FilesystemInterface $filesystem): DeleteCommand
    {
        $command = clone $this;
        $command->filesystem = $filesystem;
        return $command;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function execute(): bool
    {
        /** @var Media $media */
        $media = $this->dataValue('media');
        if (empty($media)) {
            $media = $this->mediaRepository->find($this->dataValue('mediaId'));
        }

        $mediaPath = $media->publicStatus() ? MediaPaths::PUBLIC_PATH : MediaPaths::PRIVATE_PATH;

        /**
         * move output files from mediaHandlers as well
         */
        foreach ($this->mediaHandlerSubManager->getServices() as $key => $mediaHandlerClassName) {
            /** @var MediaHandlerInterface $mediaHandler */
            $mediaHandler = $this->mediaHandlerSubManager->get($mediaHandlerClassName);

            foreach ($mediaHandler->directories() as $directory) {
                $this->deleteFolder($mediaPath . $directory . $media->basePath());
            }
        }

        $this->deleteFolder($mediaPath . $media->basePath());

        $this->mediaRepository->remove($media);

        return true;
    }

    /**
     * @return string
     */
    public static function serviceName(): string
    {
        return 'media-media-delete';
    }

    /**
     * @param $path
     */
    private function deleteFolder($path)
    {
        $content = $this->filesystem->listContents($path);

        if (\count($content) === 0) {
            return;
        }

        foreach ($content as $file) {
            $this->filesystem->delete($file['path']);
        }

        $this->filesystem->deleteDir($path);
    }
}
