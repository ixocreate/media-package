<?php
/**
 * kiwi-suite/media (https://github.com/kiwi-suite/media)
 *
 * @package kiwi-suite/media
 * @see https://github.com/kiwi-suite/media
 * @copyright Copyright (c) 2010 - 2018 kiwi suite GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace KiwiSuite\Media\Command;

use KiwiSuite\CommandBus\Command\AbstractCommand;
use KiwiSuite\Media\Config\MediaConfig;
use KiwiSuite\Media\Delegator\DelegatorInterface;
use KiwiSuite\Media\Delegator\DelegatorSubManager;
use KiwiSuite\Media\Entity\Media;
use KiwiSuite\Media\MediaCreateHandler\MediaCreateHandlerInterface;
use KiwiSuite\Media\Repository\MediaCreatedRepository;
use KiwiSuite\Media\Repository\MediaRepository;

class ChangePublicStatusCommand extends AbstractCommand
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
     * @var MediaConfig
     */
    private $mediaConfig;

    /**
     * @var MediaCreateHandlerInterface
     */
    private $mediaCreateHandler;

    /**
     * @var Media
     */
    private $media;

    /**
     * @var bool
     */
    private $publicStatus;

    /**
     * CreateCommand constructor.
     *
     * @param MediaCreatedRepository $mediaCreatedRepository
     * @param MediaRepository $mediaRepository
     * @param DelegatorSubManager $delegatorSubManager
     * @param MediaConfig $mediaConfig
     */
    public function __construct(
        MediaRepository $mediaRepository,
        DelegatorSubManager $delegatorSubManager,
        MediaConfig $mediaConfig
    ) {
        $this->mediaRepository = $mediaRepository;
        $this->delegatorSubManager = $delegatorSubManager;
        $this->mediaConfig = $mediaConfig;
    }

    /**
     * @param bool $publicStatus
     * @return CreateCommand
     */
    public function withMedia(Media $media): ChangePublicStatusCommand
    {
        $command = clone $this;
        $command->media = $media;
        return $command;
    }

    /**
     * @param bool $publicStatus
     * @return ChangePublicStatusCommand
     */
    public function withPublicStatus(bool $publicStatus): ChangePublicStatusCommand
    {
        $command = clone $this;
        $command->publicStatus = $publicStatus;
        return $command;
    }

    /**
     * @throws \Exception
     * @return bool
     */
    public function execute(): bool
    {
        $desiredPublicStatus = $this->media->publicStatus();
        if ($this->publicStatus !== null) {
            $desiredPublicStatus = $this->publicStatus;
        }

        /**
         * check if already moved
         */
        $basePath = $this->media->basePath();
        $publicDirectory = 'data/media/' . $basePath;
        $privateDirectory = 'data/media_private/' . $basePath;
        if ($desiredPublicStatus && !\file_exists($publicDirectory)) {
            $this->moveMedia($this->media, 'data/media_private/', 'data/media/');
        } elseif (!$desiredPublicStatus && !\file_exists($privateDirectory)) {
            $this->moveMedia($this->media, 'data/media/', 'data/media_private/');
        }

        if ($this->media->publicStatus() !== $desiredPublicStatus) {
            $this->media = $this->media->with('publicStatus', $desiredPublicStatus);
            $this->media = $this->media->with('updatedAt', new \DateTime());
            $this->mediaRepository->save($this->media);
        }

        return true;
    }

    /**
     * @param Media $media
     * @param string $fromStoragePath
     * @param string $toStoragePath
     * @return Media
     */
    private function moveMedia(Media $media, string $fromStoragePath, string $toStoragePath): Media
    {
        /**
         * move source file
         */
        $this->createDir($toStoragePath . $media->basePath());
        \rename($fromStoragePath . $media->basePath(), $toStoragePath . $media->basePath());

        /**
         * move output files from delegators as well
         */
        foreach ($this->delegatorSubManager->getServiceManagerConfig()->getNamedServices() as $name => $delegatorClassName) {
            /** @var DelegatorInterface $delegator */
            $delegator = $this->delegatorSubManager->get($delegatorClassName);

            if (!$delegator->isResponsible($media)) {
                continue;
            }

            foreach ($delegator->directories() as $directory) {
                $this->createDir($toStoragePath . $directory . $media->basePath());
                \rename(
                    $fromStoragePath . $directory . $media->basePath(),
                    $toStoragePath . $directory . $media->basePath()
                );
            }
        }

        return $media;
    }

    /**
     * @param string $path
     */
    private function createDir(string $path)
    {
        \mkdir($path, 0777, true);
    }

    /**
     * @return string
     */
    public static function serviceName(): string
    {
        return 'media-change-public-status';
    }
}
