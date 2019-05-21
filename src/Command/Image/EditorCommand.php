<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Command\Image;

use Ixocreate\CommandBus\Command\AbstractCommand;
use Ixocreate\Filesystem\FilesystemInterface;
use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\Entity\MediaImageInfo;
use Ixocreate\Media\ImageDefinition\ImageDefinitionSubManager;
use Ixocreate\Media\ImageDefinitionInterface;
use Ixocreate\Media\Processor\EditorProcessor;
use Ixocreate\Media\Repository\MediaImageInfoRepository;
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
     * @var MediaImageInfoRepository
     */
    private $mediaImageInfoRepository;

    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * EditorCommand constructor.
     * @param MediaRepository $mediaRepository
     * @param MediaConfig $mediaConfig
     * @param ImageDefinitionSubManager $imageDefinitionSubManager
     * @param MediaImageInfoRepository $mediaImageInfoRepository
     */
    public function __construct(
        MediaRepository $mediaRepository,
        MediaConfig $mediaConfig,
        ImageDefinitionSubManager $imageDefinitionSubManager,
        MediaImageInfoRepository $mediaImageInfoRepository
    ) {
        $this->mediaRepository = $mediaRepository;
        $this->mediaConfig = $mediaConfig;
        $this->imageDefinitionSubManager = $imageDefinitionSubManager;
        $this->mediaImageInfoRepository = $mediaImageInfoRepository;
    }

    public function withFilesystem(FilesystemInterface $filesystem): EditorCommand
    {
        $command = clone $this;
        $command->filesystem = $filesystem;
        return $command;
    }

    /**
     * @throws \Exception
     * @return bool
     */
    public function execute(): bool
    {
        /** @var Media $media */
        $media = $this->dataValue('media');
        /** @var ImageDefinitionInterface $imageDefinition */
        $imageDefinition = $this->dataValue('imageDefinition');

        $requestData = $this->dataValue('requestData');

        $mediaImageInfo = null;

        if (!empty($this->mediaImageInfoRepository->findOneBy([
            'mediaId' => $media->id(),
            'imageDefinition' => $imageDefinition::serviceName(),
        ]))) {
            /** @var MediaImageInfo $mediaImageInfo */
            $mediaImageInfo = $this->mediaImageInfoRepository->findOneBy([
                'mediaId' => $media->id(),
                'imageDefinition' => $imageDefinition::serviceName(),
            ]);

            if ($mediaImageInfo::getDefinitions()->has('updatedAt')) {
                $mediaImageInfo = $mediaImageInfo->with('updatedAt', new \DateTime());
            }

            if ($mediaImageInfo::getDefinitions()->has('cropParameters')) {
                $mediaImageInfo = $mediaImageInfo->with('cropParameters', $requestData['crop']);
            }
        }

        if (empty($this->mediaImageInfoRepository->findOneBy([
            'mediaId' => $media->id(),
            'imageDefinition' => $imageDefinition::serviceName(),
        ]))) {
            $mediaImageInfo = new MediaImageInfo([
                'id' => Uuid::uuid4(),
                'mediaId' => $media->id(),
                'imageDefinition' => $imageDefinition::serviceName(),
                'cropParameters' => $requestData['crop'],
                'createdAt' => new \DateTimeImmutable(),
                'updatedAt' => new \DateTimeImmutable(),
            ]);
        }

        (new EditorProcessor($requestData['crop'], $imageDefinition, $media, $this->mediaConfig, $this->filesystem))->process();

        $this->mediaImageInfoRepository->save($mediaImageInfo);

        return true;
    }

    public static function serviceName(): string
    {
        return 'media-image-editor';
    }
}
