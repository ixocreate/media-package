<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Command;

use Ixocreate\CommandBus\Command\AbstractCommand;
use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\Delegator\DelegatorInterface;
use Ixocreate\Media\Delegator\DelegatorSubManager;
use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\Repository\MediaRepository;

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
     * CreateCommand constructor.
     *
     * @param MediaRepository $mediaRepository
     * @param MediaConfig $mediaConfig
     */
    public function __construct(
        MediaRepository $mediaRepository,
        DelegatorSubManager $delegatorSubManager
    ) {
        $this->mediaRepository = $mediaRepository;
        $this->delegatorSubManager = $delegatorSubManager;
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

        $mediaPath = $media->publicStatus() ? 'data/media/' : 'data/media_private/';

        /**
         * move output files from delegators as well
         */
        foreach ($this->delegatorSubManager->getServiceManagerConfig()->getNamedServices() as $name => $delegatorClassName) {
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

    public static function serviceName(): string
    {
        return 'media-media-delete';
    }

    private function deleteFolder($path)
    {
        if (!\file_exists($path)) {
            return;
        }

        $files = \glob($path . '*');
        foreach ($files as $file) {
            \unlink($file);
        }

        \rmdir($path);
    }
}
