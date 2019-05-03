<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Command\Image;

use Ixocreate\CommandBus\Command\AbstractCommand;
use Ixocreate\Filesystem\FilesystemManager;
use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\Entity\MediaCrop;
use Ixocreate\Media\Exception\InvalidConfigException;
use Ixocreate\Media\ImageDefinition\ImageDefinitionSubManager;
use Ixocreate\Media\ImageDefinitionInterface;
use Ixocreate\Media\Processor\EditorProcessor;
use Ixocreate\Media\Repository\MediaCropRepository;
use Ixocreate\Media\Repository\MediaRepository;
use Ramsey\Uuid\Uuid;

final class EditorCommand extends AbstractCommand
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
     * @var ImageDefinitionSubManager
     */
    private $imageDefinitionSubManager;

    /**
     * @var MediaCropRepository
     */
    private $mediaCropRepository;

    /**
     * @var FilesystemManager
     */
    private $filesystemManager;

    /**
     * EditorCommand constructor.
     * @param MediaRepository $mediaRepository
     * @param MediaConfig $mediaConfig
     * @param ImageDefinitionSubManager $imageDefinitionSubManager
     * @param MediaCropRepository $mediaCropRepository
     * @param FilesystemManager $filesystemManager
     */
    public function __construct(
        MediaRepository $mediaRepository,
        MediaConfig $mediaConfig,
        ImageDefinitionSubManager $imageDefinitionSubManager,
        MediaCropRepository $mediaCropRepository,
        FilesystemManager $filesystemManager
    ) {
        $this->mediaRepository = $mediaRepository;
        $this->mediaConfig = $mediaConfig;
        $this->imageDefinitionSubManager = $imageDefinitionSubManager;
        $this->mediaCropRepository = $mediaCropRepository;
        $this->filesystemManager = $filesystemManager;
    }

    /**
     * @throws \Exception
     * @return bool
     */
    public function execute(): bool
    {
        if (!$this->filesystemManager->has('media')) {
            throw new InvalidConfigException('Storage Config not set');
        }

        $filesystem = $this->filesystemManager->get('media');

        /** @var Media $media */
        $media = $this->dataValue('media');
        /** @var ImageDefinitionInterface $imageDefinition */
        $imageDefinition = $this->dataValue('imageDefinition');

        $requestData = $this->dataValue('requestData');

        $mediaCrop = null;

        if (!empty($this->mediaCropRepository->findOneBy([
            'mediaId' => $media->id(),
            'imageDefinition' => $imageDefinition::serviceName(),
        ]))) {
            /** @var MediaCrop $mediaCrop */
            $mediaCrop = $this->mediaCropRepository->findOneBy([
                'mediaId' => $media->id(),
                'imageDefinition' => $imageDefinition::serviceName(),
            ]);

            if ($mediaCrop::getDefinitions()->has('updatedAt')) {
                $mediaCrop = $mediaCrop->with('updatedAt', new \DateTime());
            }

            if ($mediaCrop::getDefinitions()->has('cropParameters')) {
                $mediaCrop = $mediaCrop->with('cropParameters', $requestData['crop']);
            }
        }

        if (empty($this->mediaCropRepository->findOneBy([
            'mediaId' => $media->id(),
            'imageDefinition' => $imageDefinition::serviceName(),
        ]))) {
            $mediaCrop = new MediaCrop([
                'id' => Uuid::uuid4(),
                'mediaId' => $media->id(),
                'imageDefinition' => $imageDefinition::serviceName(),
                'cropParameters' => $requestData['crop'],
                'createdAt' => new \DateTimeImmutable(),
                'updatedAt' => new \DateTimeImmutable(),
            ]);
        }

        (new EditorProcessor($requestData['crop'], $imageDefinition, $media, $this->mediaConfig, $filesystem))->process();

        $this->mediaCropRepository->save($mediaCrop);

        return true;
    }

    public static function serviceName(): string
    {
        return 'media-image-editor';
    }
}
