<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Console;

use Ixocreate\Application\Console\CommandInterface;
use Ixocreate\Entity\EntityCollection;
use Ixocreate\Filesystem\FilesystemInterface;
use Ixocreate\Filesystem\FilesystemManager;
use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\Config\MediaPaths;
use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\Exception\InvalidArgumentException;
use Ixocreate\Media\Exception\InvalidConfigException;
use Ixocreate\Media\Handler\ImageHandler;
use Ixocreate\Media\ImageDefinition\ImageDefinitionInterface;
use Ixocreate\Media\ImageDefinition\ImageDefinitionSubManager;
use Ixocreate\Media\Processor\ImageProcessor;
use Ixocreate\Media\Repository\MediaDefinitionInfoRepository;
use Ixocreate\Media\Repository\MediaRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
     * @var ImageHandler
     */
    private $imageHandler;

    /**
     * @var FilesystemManager
     */
    private $filesystemManager;

    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var MediaDefinitionInfoRepository
     */
    private $mediaDefinitionInfoRepository;

    /**
     * RefactorImageDefinition constructor.
     *
     * @param ImageDefinitionSubManager $imageDefinitionSubManager
     * @param MediaConfig $mediaConfig
     * @param MediaRepository $mediaRepository
     * @param ImageHandler $imageHandler
     * @param FilesystemManager $filesystemManager
     * @param MediaDefinitionInfoRepository $mediaDefinitionInfoRepository
     */
    public function __construct(
        ImageDefinitionSubManager $imageDefinitionSubManager,
        MediaConfig $mediaConfig,
        MediaRepository $mediaRepository,
        ImageHandler $imageHandler,
        FilesystemManager $filesystemManager,
        MediaDefinitionInfoRepository $mediaDefinitionInfoRepository
    ) {
        parent::__construct(self::getCommandName());
        $this->imageDefinitionSubManager = $imageDefinitionSubManager;
        $this->mediaConfig = $mediaConfig;
        $this->mediaRepository = $mediaRepository;
        $this->imageHandler = $imageHandler;
        $this->filesystemManager = $filesystemManager;
        $this->mediaDefinitionInfoRepository = $mediaDefinitionInfoRepository;
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
     * @throws \League\Flysystem\FileNotFoundException
     * @throws \League\Flysystem\FileExistsException
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->filesystemManager->has('media')) {
            throw new InvalidConfigException('Storage Config not set');
        }

        $this->filesystem = $this->filesystemManager->get('media');

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
    private function generateFiles(
        ImageDefinitionInterface $imageDefinition,
        InputInterface $input,
        OutputInterface $output,
        SymfonyStyle $io
    ) {
        $mediaEntityCollection = null;
        $progressBar = null;

        // If Option "missing" ist assigned
        if ($input->getOption('missing')) {
            $mediaEntityCollection = $this->handleMissing($imageDefinition);
            if ($mediaEntityCollection->count() === 0) {
                $io->writeln(\sprintf(
                    'There are no missing Images in ImageDefinition: %s',
                    $imageDefinition::serviceName()
                ));
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
                !$this->filesystem->has(MediaPaths::PUBLIC_PATH . $imageDefinition->directory() . '/' . $media->basePath() . $media->filename()) &&
                !$this->filesystem->has(MediaPaths::PRIVATE_PATH . $imageDefinition->directory() . '/' . $media->basePath() . $media->filename())
            ) {
                if (!$this->imageHandler->isResponsible($media)) {
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
     * @throws \League\Flysystem\FileNotFoundException
     */
    private function handleChanges(
        ImageDefinitionInterface $imageDefinition,
        EntityCollection $mediaEntityCollection,
        SymfonyStyle $io,
        ProgressBar $progressBar
    ): void {
        $jsonFile = MediaPaths::PUBLIC_PATH . MediaPaths::IMAGE_DEFINITION_PATH . $imageDefinition->directory() . '/' . $imageDefinition->directory() . '.json';

        if ($this->filesystem->has($jsonFile)) {
            $json = $this->filesystem->read($jsonFile);
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
                $this->filesystem->put($jsonFile, \json_encode($json));
                $this->processImages($imageDefinition, $mediaEntityCollection, $io, $progressBar);
            }

            $io->writeln('No changes have been made in ImageDefinition: ' . $imageDefinition::serviceName());
            return;
        }

        $json['serviceName'] = $imageDefinition::serviceName();
        $json['width'] = $imageDefinition->width();
        $json['height'] = $imageDefinition->height();
        $json['mode'] = $imageDefinition->mode();
        $json['upscale'] = $imageDefinition->upscale();
        $json['directory'] = $imageDefinition->directory();
        $this->filesystem->write($jsonFile, \json_encode($json));
        $io->writeln('Created Json-File for ImageDefinition: ' . $imageDefinition::serviceName());
        $this->processImages($imageDefinition, $mediaEntityCollection, $io, $progressBar);
    }

    /**
     * @param ImageDefinitionInterface $imageDefinition
     * @param EntityCollection $mediaEntityCollection
     * @param SymfonyStyle $io
     * @param ProgressBar $progressBar
     */
    private function processImages(
        ImageDefinitionInterface $imageDefinition,
        EntityCollection $mediaEntityCollection,
        SymfonyStyle $io,
        ProgressBar $progressBar
    ) {
        $progressBar->start();

        $array = $mediaEntityCollection->toArray();

        foreach ($array as $media) {
            if (!$this->imageHandler->isResponsible($media)) {
                continue;
            }

            // In Case that there is a Crop-Entry, remove it due to reset
            $mediaCropResult = $this->mediaDefinitionInfoRepository->findBy(['mediaId' => $media->id(), 'imageDefinition' => $imageDefinition::serviceName()]);
            if (!empty($mediaCropResult)) {
                foreach ($mediaCropResult as $mediaCrop) {
                    $this->mediaDefinitionInfoRepository->remove($mediaCrop);
                }
            }

            $imageProcessor = new ImageProcessor($media, $imageDefinition, $this->mediaConfig, $this->filesystem);
            $imageProcessor->process();
            $progressBar->advance();
        }
        $progressBar->finish();
        $io->newLine();
        $io->writeln(\sprintf('Finished'));
    }
}
