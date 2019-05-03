<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Command\Media;

use Ixocreate\CommandBus\Command\AbstractCommand;
use Ixocreate\Filesystem\FilesystemManager;
use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\Exception\InvalidConfigException;
use Ixocreate\Media\Handler\HandlerInterface;
use Ixocreate\Media\Handler\MediaHandlerSubManager;
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
    private $delegatorSubManager;

    /**
     * @var FilesystemManager
     */
    private $filesystemManager;

    /**
     * CreateCommand constructor.
     *
     * @param MediaRepository $mediaRepository
     * @param MediaHandlerSubManager $delegatorSubManager
     * @param FilesystemManager $filesystemManager
     */
    public function __construct(
        MediaRepository $mediaRepository,
        MediaHandlerSubManager $delegatorSubManager,
        FilesystemManager $filesystemManager
    ) {
        $this->mediaRepository = $mediaRepository;
        $this->delegatorSubManager = $delegatorSubManager;
        $this->filesystemManager = $filesystemManager;
    }

    /**
     * @throws \Exception
     * @return bool
     */
    public function execute(): bool
    {
        /** @var Media $media */
        $media = $this->dataValue('media');
        if (empty($media)) {
            $media = $this->mediaRepository->find($this->dataValue('mediaId'));
        }

        if (!$this->filesystemManager->has('media')) {
            throw new InvalidConfigException('Storage Config not set');
        }

        $mediaPath = $media->publicStatus() ? MediaPaths::PUBLIC_PATH : MediaPaths::PRIVATE_PATH;

        /**
         * move output files from delegators as well
         */
        foreach ($this->delegatorSubManager->getServices() as $key => $delegatorClassName) {
            /** @var HandlerInterface $delegator */
            $delegator = $this->delegatorSubManager->get($delegatorClassName);

            foreach ($delegator->directories() as $directory) {
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
     * @throws \League\Flysystem\FileNotFoundException
     */
    private function deleteFolder($path)
    {
        $filesystem = $this->filesystemManager->get("media");
        $content = $filesystem->listContents($path);

        if (\count($content) === 0) {
            return;
        }

        foreach ($content as $file) {
            $filesystem->delete($file['path']);
        }

        $filesystem->deleteDir($path);
    }
}
