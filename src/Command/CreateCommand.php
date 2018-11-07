<?php

declare(strict_types=1);

namespace KiwiSuite\Media\Command;

use Cocur\Slugify\Slugify;
use KiwiSuite\Admin\Entity\User;
use KiwiSuite\CommandBus\Command\AbstractCommand;
use KiwiSuite\Media\Config\MediaConfig;
use KiwiSuite\Media\Delegator\DelegatorInterface;
use KiwiSuite\Media\Delegator\DelegatorSubManager;
use KiwiSuite\Media\Entity\Media;
use KiwiSuite\Media\Entity\MediaCreated;
use KiwiSuite\Media\MediaCreateHandler\MediaCreateHandlerInterface;
use KiwiSuite\Media\Repository\MediaCreatedRepository;
use KiwiSuite\Media\Repository\MediaRepository;

class CreateCommand extends AbstractCommand
{
    /**
     * @var MediaCreatedRepository
     */
    private $mediaCreatedRepository;

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
     * @var User
     */
    private $createdUser;

    /**
     * @var bool
     */
    private $checkForDuplicates = true;

    /**
     * CreateCommand constructor.
     * @param MediaCreatedRepository $mediaCreatedRepository
     * @param MediaRepository $mediaRepository
     * @param DelegatorSubManager $delegatorSubManager
     * @param MediaConfig $mediaConfig
     */
    public function __construct(
        MediaCreatedRepository $mediaCreatedRepository,
        MediaRepository $mediaRepository,
        DelegatorSubManager $delegatorSubManager,
        MediaConfig $mediaConfig
    ) {
        $this->mediaCreatedRepository = $mediaCreatedRepository;
        $this->mediaRepository = $mediaRepository;
        $this->delegatorSubManager = $delegatorSubManager;
        $this->mediaConfig = $mediaConfig;
    }

    /**
     * @param MediaCreateHandlerInterface $mediaCreateHandler
     * @return CreateCommand
     */
    public function withMediaCreateHandler(MediaCreateHandlerInterface $mediaCreateHandler): CreateCommand
    {
        $command = clone $this;
        $command->mediaCreateHandler = $mediaCreateHandler;
        return $command;
    }

    /**
     * @param User $user
     * @return CreateCommand
     */
    public function withCreatedUser(User $user): CreateCommand
    {
        $command = clone $this;
        $command->createdUser = $user;
        return $command;
    }

    /**
     * @param bool $checkForDuplicates
     * @return CreateCommand
     */
    public function withCheckForDuplicates(bool $checkForDuplicates): CreateCommand
    {
        $command = clone $this;
        $command->checkForDuplicates = $checkForDuplicates;
        return $command;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function execute(): bool
    {
        if (!($this->checkWhitelist($this->mediaCreateHandler->tempFile()))) {
            return false;
        }

        if ($this->checkForDuplicates && $this->checkDuplicate($this->mediaCreateHandler->tempFile())) {
            return false;
        }

        $media = $this->prepareMedia();

        $this->mediaRepository->save($media);

        foreach ($this->delegatorSubManager->getServiceManagerConfig()->getNamedServices() as $name => $delegator) {
            /** @var DelegatorInterface $delegator */
            $delegator = $this->delegatorSubManager->get($delegator);
            if (!$delegator->isResponsible($media)) {
                continue;
            }
            $delegator->process($media);
        }

        if ($this->createdUser !== null) {
            $mediaCreated = new MediaCreated([
                'mediaId' => $media->id(),
                'createdBy' => $this->createdUser->id()
            ]);
            $this->mediaCreatedRepository->save($mediaCreated);
        }

        return true;
    }

    /**
     * @return Media
     * @throws \Exception
     */
    private function prepareMedia(): Media
    {
        $basePath = $this->createDir();
        $filenameParts = \pathinfo($this->mediaCreateHandler->filename());
        $slugify = new Slugify();
        $filename = $slugify->slugify($filenameParts['filename']) . '.' . $filenameParts['extension'];

        $this->mediaCreateHandler->move('data/media/' . $basePath . $filename);

        $finfo = \finfo_open(FILEINFO_MIME_TYPE);

        $media = new Media([
            'id' => $this->uuid(),
            'basePath' => $basePath,
            'filename' => $filename,
            'mimeType' => \finfo_file($finfo, 'data/media/' . $basePath . $filename),
            'size' => \sprintf('%u', filesize('data/media/' . $basePath . $filename)),
            'publicStatus' => false,
            'hash' => \hash_file('sha256','data/media/' . $basePath . $filename),
            'createdAt' => new \DateTimeImmutable(),
            'updatedAt' => new \DateTimeImmutable(),
        ]);

        return $media;
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function createDir(): string
    {
        do {
            $basePath = \implode('/', \str_split(\bin2hex(\random_bytes(3)), 2)) . '/';
            $exists = \is_dir('data/media/' . $basePath);
        } while ($exists === true);

        \mkdir('data/media/' . $basePath, 0777, true);

        return $basePath;
    }

    /**
     * @param $upload
     * @return bool
     */
    private function checkDuplicate(string $file): bool
    {
        $count = $this->mediaRepository->count(['hash' => \hash_file('sha256', $file)]);
        return ($count > 0);
    }

    /**
     * @param $upload
     * @return bool
     */
    private function checkWhitelist(string $file): bool
    {
        $finfo = \finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = \finfo_file($finfo, $file);
        return \in_array($mimeType, $this->mediaConfig->whitelist());
    }

    public static function serviceName(): string
    {
        return 'media-media-create';
    }
}
