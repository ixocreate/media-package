<?php
declare(strict_types=1);

namespace Ixocreate\Media\Command\Media;

use Ixocreate\CommandBus\Command\AbstractCommand;
use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\ImageDefinition\ImageDefinitionSubManager;
use Ixocreate\Media\Repository\MediaRepository;
use Ixocreate\Media\Entity\Media;

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
     * UpdateCommand constructor.
     * @param MediaRepository $mediaRepository
     * @param MediaConfig $mediaConfig
     */
    public function __construct(MediaRepository $mediaRepository, MediaConfig $mediaConfig, ImageDefinitionSubManager $imageDefinitionSubManager)
    {
        $this->mediaRepository = $mediaRepository;
        $this->mediaConfig = $mediaConfig;
        foreach ($imageDefinitionSubManager->getServiceManagerConfig()->getNamedServices() as $name => $className) {
            var_dump($className);
            var_dump($name);
        }
        foreach ($imageDefinitionSubManager->getServices() as $name => $className) {
            var_dump($className);
            var_dump($name);
        }
        die('d');
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
            return false;
        }

        if ($this->mediaConfig->publicStatus()) {
            $desiredPublicStatus = $data['publicStatus'];
            if ($media->publicStatus() !== $desiredPublicStatus) {
                // TODO: DO SOMETHING
            }
        }

        $newFilename = $data['newFilename'];

        if ($media->filename() !== $newFilename) {
            // TODO: DO SOMETHING
        }

        return true;
    }

    public static function serviceName(): string
    {
        return 'media-media-update';
    }
}