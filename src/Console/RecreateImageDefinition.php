<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Console;

use Ixocreate\Contract\Media\ImageDefinitionInterface;
use Ixocreate\Entity\Entity\EntityCollection;
use Ixocreate\Filesystem\Storage\StorageSubManager;
use Ixocreate\Media\Delegator\Delegators\Image;
use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\Exception\InvalidConfigException;
use Ixocreate\Media\MediaPaths;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Console\Command\Command;
use Ixocreate\Contract\Command\CommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ixocreate\Media\ImageDefinition\ImageDefinitionSubManager;
use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\Repository\MediaRepository;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Ixocreate\Media\Processor\ImageProcessor;
use Ixocreate\Media\Exception\InvalidArgumentException;

final class RecreateImageDefinition extends Command implements CommandInterface
{
    /*
     * @var ImageDefinitionSubManager
     */
    private $imageDefinitionSubManager;

    /**
     * @var MediaConfig
     */
    private $mediaConfig;

    /**
     * @var MediaRepository
     */
    private $mediaRepository;

    /**
     * @var Image
     */
    private $imageDelegator;

    /**
     * @var StorageSubManager
     */
    private $storageSubManager;

    /**
     * @var FilesystemInterface
     */
    private $storage;

    /**
     * RefactorImageDefinition constructor.
     * @param ImageDefinitionSubManager $imageDefinitionSubManager
     * @param MediaConfig $mediaConfig
     * @param MediaRepository $mediaRepository
     * @param Image $imageDelegator
     * @param StorageSubManager $storageSubManager
     */
    public function __construct(
        ImageDefinitionSubManager $imageDefinitionSubManager,
        MediaConfig $mediaConfig,
        MediaRepository $mediaRepository,
        Image $imageDelegator,
        StorageSubManager $storageSubManager
    ) {
        parent::__construct(self::getCommandName());
        $this->imageDefinitionSubManager = $imageDefinitionSubManager;
        $this->mediaConfig = $mediaConfig;
        $this->mediaRepository = $mediaRepository;
        $this->imageDelegator = $imageDelegator;
        $this->storageSubManager = $storageSubManager;
    }

    public function configure()
    {
        $this
            ->setDescription("Recreates files of an ImageDefinition")
            ->addArgument('name', InputArgument::OPTIONAL, 'Name of specific ImageDefinition to be refactored')
            ->addOption('all', null, null, 'All ImageDefinitions will be refactored')
            ->addOption('missing', 'm', null, 'Only missing files will be created')
            ->addOption('changed', 'c', null, 'Only files of changed ImageDefinitions will be created');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \League\Flysystem\FileExistsException
     * @throws \League\Flysystem\FileNotFoundException
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->storageSubManager->has('media')) {
            throw new InvalidConfigException('Storage Config not set');
        }

        $this->storage = $this->storageSubManager->get('media');

        $io = new SymfonyStyle($input, $output);
        $io->title('Refactor ImageDefinition');
        $this->evaluateInput($input, $output, $io);
    }

    /**
     * @return string
     */
    public static function getCommandName()
    {
        return 'media:recreate-image-definition';
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param SymfonyStyle $io
     * @throws \League\Flysystem\FileExistsException
     * @throws \League\Flysystem\FileNotFoundException
     */
    private function evaluateInput(InputInterface $input, OutputInterface $output, SymfonyStyle $io)
    {
        if ($input->getOption('all') && empty($input->getArgument('name'))) {
            foreach ($this->imageDefinitionSubManager->getServices() as $imageDefinitionClassName) {
                /** @var ImageDefinitionInterface $imageDefinition */
                $imageDefinition = $this->imageDefinitionSubManager->get($imageDefinitionClassName);
                $this->generateFiles($imageDefinition, $input, $output, $io);
            }
        }

        if (!$input->getOption('all') && !empty($input->getArgument('name'))) {
            $inputName = $input->getArgument('name');
            $inputName = \trim($inputName);
            if (!\in_array(
                $inputName,
                \array_keys($this->imageDefinitionSubManager->getServiceManagerConfig()->getNamedServices())
            )) {
                throw new InvalidArgumentException(\sprintf("ImageDefinition '%s' does not exist", $inputName));
            }
        }
        if (isset($inputName)) {
            /** @var ImageDefinitionInterface $imageDefinition */
            $imageDefinition = $this->imageDefinitionSubManager->get($inputName);
            $this->generateFiles($imageDefinition, $input, $output, $io);
        }

        if (!$input->getOption('all') && empty($input->getArgument('name'))) {
            $io->writeln('Please enter Option "--all" or enter a valid ImageDefinition name');
        }

        if ($input->getOption('all') && !empty($input->getArgument('name'))) {
            $io->writeln('You only can either use "--all" or specifiy a valid ImageDefinition name');
        }
    }

    /**
     * @param OutputInterface $output
     * @param ImageDefinitionInterface $imageDefinition
     * @param $count
     * @return ProgressBar
     */
    private function customProgressBar(OutputInterface $output, ImageDefinitionInterface $imageDefinition, $count)
    {
        $progressBar = new ProgressBar($output, $count);
        ProgressBar::setFormatDefinition('custom', '%message% -- %current%/%max% [%bar%] -- %percent:3s%%');
        $progressBar->setFormat('custom');
        $progressBar->setProgressCharacter("\xF0\x9F\x8D\xBA");
        $progressBar->setMessage('ImageDefinition: ' . $imageDefinition::serviceName());
        return $progressBar;
    }

    /**
     * @param ImageDefinitionInterface $imageDefinition
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param SymfonyStyle $io
     * @throws \League\Flysystem\FileExistsException
     * @throws \League\Flysystem\FileNotFoundException
     */
    private function generateFiles(ImageDefinitionInterface $imageDefinition, InputInterface $input, OutputInterface $output, SymfonyStyle $io)
    {
        $mediaEntityCollection = null;
        $progressBar = null;

        // If Option "missing" ist assigned
        if ($input->getOption('missing')) {
            $mediaEntityCollection = $this->handleMissing($imageDefinition);
            if ($mediaEntityCollection->count() === 0) {
                $io->writeln(\sprintf('There are no missing Images in ImageDefinition: %s', $imageDefinition::serviceName()));
                return;
            }
            // If Option "changed" is assigned
            $progressBar = $this->customProgressBar($output, $imageDefinition, $mediaEntityCollection->count());
            if ($input->getOption('changed')) {
                $this->handleChanges($imageDefinition, $mediaEntityCollection, $io, $progressBar);
                return;
            }
        }
        // If Option "missing" is NOT assigned
        if (!$input->getOption('missing')) {
            $mediaEntityCollection = new EntityCollection($this->mediaRepository->findAll());
            $progressBar = $this->customProgressBar($output, $imageDefinition, $mediaEntityCollection->count());
            // If Option "changed" is assigned
            if ($input->getOption('changed')) {
                $this->handleChanges($imageDefinition, $mediaEntityCollection, $io, $progressBar);
                return;
            }
        }

        return $this->processImages($imageDefinition, $mediaEntityCollection, $io, $progressBar);
    }

    /**
     * @param ImageDefinitionInterface $imageDefinition
     * @return EntityCollection
     */
    private function handleMissing(ImageDefinitionInterface $imageDefinition)
    {
        $mediaCollection = [];
        foreach ($this->mediaRepository->findAll() as $media) {
            /** @var Media $media */
            if (
                !$this->storage->has(MediaPaths::PUBLIC_PATH . $imageDefinition->directory() . '/' . $media->basePath() . $media->filename()) &&
                !$this->storage->has(MediaPaths::PRIVATE_PATH . $imageDefinition->directory() . '/' . $media->basePath() . $media->filename())
            ) {
                if (!$this->imageDelegator->isResponsible($media)) {
                    continue;
                }
                $mediaCollection [] = $media;
            }
        }
        return new EntityCollection($mediaCollection);
    }

    /**
     * @param ImageDefinitionInterface $imageDefinition
     * @param EntityCollection $mediaEntityCollection
     * @param SymfonyStyle $io
     * @param ProgressBar $progressBar
     * @throws \League\Flysystem\FileExistsException
     * @throws \League\Flysystem\FileNotFoundException
     */
    private function handleChanges(ImageDefinitionInterface $imageDefinition, EntityCollection $mediaEntityCollection, SymfonyStyle $io, ProgressBar $progressBar)
    {
        $jsonFiles = [
            'publicJsonFile' => MediaPaths::PUBLIC_PATH . MediaPaths::IMAGE_DEFINITION_PATH . $imageDefinition->directory() . '/' . $imageDefinition->directory() . '.json',
            'privateJsonFile' => MediaPaths::PRIVATE_PATH . MediaPaths::IMAGE_DEFINITION_PATH . $imageDefinition->directory() . '/' . $imageDefinition->directory() . '.json',
        ];

        foreach ($jsonFiles as $type => $file) {
            if ($this->storage->has($file)) {
                $json = $this->storage->read($file);
                $json = \json_decode($json, true);
                if (
                    $json['width'] != $imageDefinition->width() ||
                    $json['height'] != $imageDefinition->height() ||
                    $json['mode'] != $imageDefinition->mode() ||
                    $json['upscale'] != $imageDefinition->upscale()
                ) {
                    $json['width'] = $imageDefinition->width();
                    $json['height'] = $imageDefinition->height();
                    $json['mode'] = $imageDefinition->mode();
                    $json['upscale'] = $imageDefinition->upscale();
                    $this->storage->put($file, \json_encode($json));
                    return $this->processImages($imageDefinition, $mediaEntityCollection, $io, $progressBar);
                }
            }

            if (!$this->storage->has($file)) {
                $json['serviceName'] = $imageDefinition::serviceName();
                $json['width'] = $imageDefinition->width();
                $json['height'] = $imageDefinition->height();
                $json['mode'] = $imageDefinition->mode();
                $json['upscale'] = $imageDefinition->upscale();
                $json['directory'] = $imageDefinition->directory();
                $this->storage->write($file, \json_encode($json));
                return $this->processImages($imageDefinition, $mediaEntityCollection, $io, $progressBar);
            }
        }

        return $io->writeln('No changes have been made in ImageDefinition: ' . $imageDefinition::serviceName());
    }

    /**
     * @param ImageDefinitionInterface $imageDefinition
     * @param EntityCollection $mediaEntityCollection
     * @param SymfonyStyle $io
     * @param ProgressBar $progressBar
     * @throws \League\Flysystem\FileNotFoundException
     */
    private function processImages(ImageDefinitionInterface $imageDefinition, EntityCollection $mediaEntityCollection, SymfonyStyle $io, ProgressBar $progressBar)
    {
        $progressBar->start();

        $array = $mediaEntityCollection->toArray();

        foreach ($array as $media) {
            if (!$this->imageDelegator->isResponsible($media)) {
                continue;
            }

            $imageProcessor = new ImageProcessor($media, $imageDefinition, $this->mediaConfig, $this->storage);
            $imageProcessor->process();
            $progressBar->advance();
        }
        $progressBar->finish();
        $io->newLine();
        $io->writeln(\sprintf('Finished'));
    }
}
