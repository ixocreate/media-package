<?php
declare(strict_types=1);

namespace Ixocreate\Media\Command;

use Ixocreate\CommandBus\Command\AbstractCommand;
use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\Repository\MediaRepository;
use Ixocreate\Media\Entity\Media;

final class UpdateCommand extends AbstractCommand
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
     * UpdateCommand constructor.
     * @param MediaRepository $mediaRepository
     * @param MediaConfig $mediaConfig
     */
    public function __construct(MediaRepository $mediaRepository, MediaConfig $mediaConfig)
    {
        $this->mediaRepository = $mediaRepository;
        $this->mediaConfig = $mediaConfig;
    }

    /**
     * @return bool
     */
    public function execute(): bool
    {
        $data = $this->data();

        /** @var Media $media */
        $media = $this->mediaRepository->find($data['id']);

        if ($media === null) {
            return new ApiErrorResponse('given media Id does not exist');
        }

        if ($this->mediaConfig->publicStatus()) {
            $desiredPublicStatus = $data['publicStatus'];
            if ($media->publicStatus() !== $desiredPublicStatus) {
                // TODO: DO SOMETHING
            }
        }

        $newFilename = $data['newFilname'];

        if ($media->filename() !== $newFilename) {
            // TODO: DO SOMETHING
        }

        return true;
    }
}