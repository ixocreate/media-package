<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Package\Command\Media;

use Ixocreate\CommandBus\Package\Command\AbstractCommand;
use Ixocreate\Media\Package\DelegatorInterface;
use Ixocreate\Filesystem\Package\Storage\StorageSubManager;
use Ixocreate\Media\Package\Delegator\DelegatorSubManager;
use Ixocreate\Media\Package\Entity\Media;
use Ixocreate\Media\Package\Exception\InvalidConfigException;
use Ixocreate\Media\Package\MediaPaths;
use Ixocreate\Media\Package\Repository\MediaRepository;
use League\Flysystem\FilesystemInterface;

class DeleteCommand extends AbstractCommand
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
     * @param StorageSubManager $storageSubManager
     */
    public function __construct(
        MediaRepository $mediaRepository,
        DelegatorSubManager $delegatorSubManager,
        StorageSubManager $storageSubManager
    ) {
        $this->mediaRepository = $mediaRepository;
        $this->delegatorSubManager = $delegatorSubManager;
        $this->storageSubManager = $storageSubManager;
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

        if (!$this->storageSubManager->has('media')) {
            throw new InvalidConfigException('Storage Config not set');
        }

        $this->storage = $this->storageSubManager->get('media');

        $mediaPath = $media->publicStatus() ? MediaPaths::PUBLIC_PATH : MediaPaths::PRIVATE_PATH;

        /**
         * move output files from delegators as well
         */
        foreach ($this->delegatorSubManager->getServices() as $key => $delegatorClassName) {
            /** @var DelegatorInterface $delegator */
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
        $content = $this->storage->listContents($path);

        if (\count($content) === 0) {
            return;
        }

        foreach ($content as $file) {
            $this->storage->delete($file['path']);
        }

        $this->storage->deleteDir($path);
    }
}
