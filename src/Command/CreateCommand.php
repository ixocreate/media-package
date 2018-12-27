<?php
/**
 * @see https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Command;

use Cocur\Slugify\Slugify;
use Ixocreate\Admin\Entity\User;
use Ixocreate\CommandBus\Command\AbstractCommand;
use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\Delegator\DelegatorInterface;
use Ixocreate\Media\Delegator\DelegatorSubManager;
use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\Entity\MediaCreated;
use Ixocreate\Media\Exception\FileDuplicateException;
use Ixocreate\Media\Exception\FileTypeNotSupportedException;
use Ixocreate\Media\MediaCreateHandler\MediaCreateHandlerInterface;
use Ixocreate\Media\Repository\MediaCreatedRepository;
use Ixocreate\Media\Repository\MediaRepository;

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
     * @var bool
     */
    private $publicStatus = true;

    /**
     * CreateCommand constructor.
     *
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
     * @param bool $publicStatus
     * @return CreateCommand
     */
    public function withPublicStatus(bool $publicStatus): CreateCommand
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
        if (!($this->checkWhitelist($this->mediaCreateHandler->tempFile()))) {
            throw new FileTypeNotSupportedException();
        }

        if ($this->checkForDuplicates && $this->checkDuplicate($this->mediaCreateHandler->tempFile())) {
            throw new FileDuplicateException();
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
                'createdBy' => $this->createdUser->id(),
            ]);
            $this->mediaCreatedRepository->save($mediaCreated);
        }

        return true;
    }

    /**
     * @throws \Exception
     * @return Media
     */
    private function prepareMedia(): Media
    {
        $storageDir = $this->publicStatus ? 'data/media/' : 'data/media_private/';
        $basePath = $this->createDir($storageDir);
        $filenameParts = \pathinfo($this->mediaCreateHandler->filename());
        $slugify = new Slugify();
        $filename = $slugify->slugify($filenameParts['filename']) . '.' . $filenameParts['extension'];

        $this->mediaCreateHandler->move($storageDir . $basePath . $filename);
        $finfo = \finfo_open(FILEINFO_MIME_TYPE);

        $media = new Media([
            'id' => $this->uuid(),
            'basePath' => $basePath,
            'filename' => $filename,
            'mimeType' => \finfo_file($finfo, $storageDir . $basePath . $filename),
            'size' => \sprintf('%u', \filesize($storageDir . $basePath . $filename)),
            'publicStatus' => $this->publicStatus,
            'hash' => \hash_file('sha256', $storageDir . $basePath . $filename),
            'createdAt' => new \DateTimeImmutable(),
            'updatedAt' => new \DateTimeImmutable(),
        ]);

        return $media;
    }

    /**
     * @param string $directory
     * @throws \Exception
     * @return string
     */
    private function createDir(string $directory): string
    {
        do {
            $basePath = \implode('/', \str_split(\bin2hex(\random_bytes(3)), 2)) . '/';
            $exists = \is_dir($directory . $basePath);
        } while ($exists === true);

        \mkdir($directory . $basePath, 0777, true);

        return $basePath;
    }

    /**
     * @param string $file
     * @return bool
     */
    private function checkDuplicate(string $file): bool
    {
        $count = $this->mediaRepository->count(['hash' => \hash_file('sha256', $file)]);
        return $count > 0;
    }

    /**
     * @param string $file
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
