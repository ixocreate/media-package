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
use Ixocreate\Media\Entity\MediaDefinitionInfo;
use Ixocreate\Media\ImageDefinition\ImageDefinitionSubManager;
use Ixocreate\Media\ImageDefinitionInterface;
use Ixocreate\Media\Processor\EditorProcessor;
use Ixocreate\Media\Repository\MediaDefinitionInfoRepository;
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
     * @var MediaDefinitionInfoRepository
     */
    private $mediaDefinitionInfoRepository;

    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * EditorCommand constructor.
     * @param MediaRepository $mediaRepository
     * @param MediaConfig $mediaConfig
     * @param ImageDefinitionSubManager $imageDefinitionSubManager
     * @param MediaDefinitionInfoRepository $mediaDefinitionInfoRepository
     */
    public function __construct(
        MediaRepository $mediaRepository,
        MediaConfig $mediaConfig,
        ImageDefinitionSubManager $imageDefinitionSubManager,
        MediaDefinitionInfoRepository $mediaDefinitionInfoRepository
    ) {
        $this->mediaRepository = $mediaRepository;
        $this->mediaConfig = $mediaConfig;
        $this->imageDefinitionSubManager = $imageDefinitionSubManager;
        $this->mediaDefinitionInfoRepository = $mediaDefinitionInfoRepository;
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

        $mediaDefinitionInfo = null;

        if (!empty($this->mediaDefinitionInfoRepository->findOneBy([
            'mediaId' => $media->id(),
            'imageDefinition' => $imageDefinition::serviceName(),
        ]))) {
            /** @var MediaDefinitionInfo $mediaDefinitionInfo */
            $mediaDefinitionInfo = $this->mediaDefinitionInfoRepository->findOneBy([
                'mediaId' => $media->id(),
                'imageDefinition' => $imageDefinition::serviceName(),
            ]);

            if ($mediaDefinitionInfo::getDefinitions()->has('updatedAt')) {
                $mediaDefinitionInfo = $mediaDefinitionInfo->with('updatedAt', new \DateTime());
            }

            if ($mediaDefinitionInfo::getDefinitions()->has('cropParameters')) {
                $mediaDefinitionInfo = $mediaDefinitionInfo->with('cropParameters', $requestData['crop']);
            }
        }

        if (empty($this->mediaDefinitionInfoRepository->findOneBy([
            'mediaId' => $media->id(),
            'imageDefinition' => $imageDefinition::serviceName(),
        ]))) {
            $mediaDefinitionInfo = new MediaDefinitionInfo([
                'id' => Uuid::uuid4(),
                'mediaId' => $media->id(),
                'imageDefinition' => $imageDefinition::serviceName(),
                'cropParameters' => $requestData['crop'],
                'createdAt' => new \DateTimeImmutable(),
                'updatedAt' => new \DateTimeImmutable(),
            ]);
        }

        (new EditorProcessor($requestData['crop'], $imageDefinition, $media, $this->mediaConfig, $this->filesystem))->process();

        $this->mediaDefinitionInfoRepository->save($mediaDefinitionInfo);

        return true;
    }

    public static function serviceName(): string
    {
        return 'media-image-editor';
    }
}
