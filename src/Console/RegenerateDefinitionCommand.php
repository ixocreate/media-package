<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Console;

use Ixocreate\Application\Console\CommandInterface;
use Ixocreate\CommandBus\CommandBus;
use Ixocreate\Filesystem\FilesystemInterface;
use Ixocreate\Filesystem\FilesystemManager;
use Ixocreate\Media\Command\Image\EditorCommand;
use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\Config\MediaPaths;
use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\Entity\MediaDefinitionInfo;
use Ixocreate\Media\Exception\InvalidConfigException;
use Ixocreate\Media\Handler\ImageHandler;
use Ixocreate\Media\ImageDefinition\ImageDefinitionInterface;
use Ixocreate\Media\ImageDefinition\ImageDefinitionSubManager;
use Ixocreate\Media\MediaInterface;
use Ixocreate\Media\Repository\MediaDefinitionInfoRepository;
use Ixocreate\Media\Repository\MediaRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class RegenerateDefinitionCommand extends Command implements CommandInterface
{
    /**
     * @var array
     */
    private $cropParameters = [];

    /**
     * @var MediaConfig
     */
    private $mediaConfig;

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
     * @var MediaRepository
     */
    private $mediaRepository;

    /**
     * @var ImageDefinitionSubManager
     */
    private $imageDefinitionSubManager;

    /**
     * @var MediaDefinitionInfoRepository
     */
    private $mediaDefinitionInfoRepository;

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * RegenerateDefinition constructor.
     *
     * @param MediaConfig $mediaConfig
     * @param ImageHandler $imageHandler
     * @param FilesystemManager $filesystemManager
     * @param MediaRepository $mediaRepository
     * @param ImageDefinitionSubManager $imageDefinitionSubManager
     * @param MediaDefinitionInfoRepository $mediaDefinitionInfoRepository
     * @param CommandBus $commandBus
     */
    public function __construct(
        MediaConfig $mediaConfig,
        ImageHandler $imageHandler,
        FilesystemManager $filesystemManager,
        MediaRepository $mediaRepository,
        ImageDefinitionSubManager $imageDefinitionSubManager,
        MediaDefinitionInfoRepository $mediaDefinitionInfoRepository,
        CommandBus $commandBus
    ) {
        parent::__construct(self::getCommandName());
        $this->imageDefinitionSubManager = $imageDefinitionSubManager;
        $this->mediaDefinitionInfoRepository = $mediaDefinitionInfoRepository;
        $this->mediaConfig = $mediaConfig;
        $this->mediaRepository = $mediaRepository;
        $this->filesystemManager = $filesystemManager;
        $this->imageHandler = $imageHandler;
        $this->commandBus = $commandBus;
    }

    /**
     * Command Name
     *
     * @return string
     */
    public static function getCommandName()
    {
        return 'media:regenerate-definition';
    }

    /**
     * Command Config
     */
    public function configure()
    {
        $this
            ->setDescription('Regenerates or generates ImageDefinition related files')
            // TODO: Define useful Help Text
            ->setHelp('Useful Help Text')
            ->setDefinition(
                new InputDefinition([
                    new InputArgument('name', InputArgument::OPTIONAL, 'Name of specific ImageDefinition to be regenerated'),
                    new InputOption('all', 'a', null, 'All Definitions will be regenerated'),
                    new InputOption('changed', 'c', null, 'Only files of changed ImageDefinitions will be regenerated'),
                ])
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->filesystemManager->has('media')) {
            throw new InvalidConfigException('storage config not set');
        }

        $this->filesystem = $this->filesystemManager->get('media');

        $style = new SymfonyStyle($input, $output);
        $style->title('Regenerate Definition');

        if (!$input->getOption('all') && !$input->getOption('changed') && !$input->getArgument('name')) {
            $style->error('Please enter a valid option or specify a valid ImageDefinition "name"');
            return 1;
        }

        if ($input->getArgument('name')) {
            if ($input->getOption('all') || $input->getOption('changed')) {
                $style->error('regeneration of a specific ImageDefinition can not be combined with "--all" or "--changed"');
                return 1;
            }

            $inputName = \trim($input->getArgument('name'));
            $inputName = \mb_strtolower($inputName);

            $this->runSpecific($inputName, $output, $style);
            return;
        }

        if ($input->getOption('changed') && $input->getOption('all')) {
            $style->error('You can not use option "--changed" with option "--all"');
            return 1;
        }

        // In Case all Definitions should be checked
        if ($input->getOption('all')) {
            $this->runAll($output, $style);
            return;
        }

        $this->runChanged($output, $style);
    }

    /**
     * Method is used if the options "all" was selected
     *
     * @param OutputInterface $output
     * @param SymfonyStyle $style
     * @throws \Exception
     */
    private function runAll(OutputInterface $output, SymfonyStyle $style)
    {
        foreach ($this->imageDefinitionSubManager->services() as $imageDefinitionClassName) {
            $imageDefinition = $this->imageDefinitionSubManager->get($imageDefinitionClassName);

            $this->processImages($imageDefinition, $output, $style);
        }
    }

    /**
     * Method is used if the argument "name" was filled
     *
     * @param string $name
     * @param OutputInterface $output
     * @param SymfonyStyle $style
     * @throws \Exception
     */
    private function runSpecific(string $name, OutputInterface $output, SymfonyStyle $style)
    {
        if (!$this->imageDefinitionSubManager->has($name)) {
            $style->error(\sprintf("ImageDefinition '%s' does not exist", $name));
            return;
        }

        $imageDefinition = $this->imageDefinitionSubManager->get($name);
        $this->processImages($imageDefinition, $output, $style);
    }

    /**
     * @param OutputInterface $output
     * @param SymfonyStyle $style
     * @throws \Exception
     */
    private function runChanged(OutputInterface $output, SymfonyStyle $style)
    {
        $changedDefinitions = [];
        foreach ($this->imageDefinitionSubManager->services() as $imageDefinitionClassName) {
            $imageDefinition = $this->imageDefinitionSubManager->get($imageDefinitionClassName);

            if ($this->checkDefinitionChanges($imageDefinition)) {
                $changedDefinitions[] = $imageDefinition;
                $style->writeln(\sprintf("change detected for '%s'", $imageDefinition));
            }
        }

        if (empty($changedDefinitions)) {
            $style->writeln('noting to regenerate, no ImageDefinition changed');
        }

        foreach ($changedDefinitions as $changedDefinition) {
            $this->processImages($changedDefinition, $output, $style);
        }
    }

    /**
     * Checks if there is a existing .json File.
     * If not a new one will be created with the ImageDefinition credentials.
     *
     * Returns "true" if a new .json File was created
     * Returns "false" if there is a .json File already
     *
     * @param ImageDefinitionInterface $imageDefinition
     * @return bool
     */
    private function checkJsonFile(ImageDefinitionInterface $imageDefinition): bool
    {
        $jsonFile = MediaPaths::PUBLIC_PATH . MediaPaths::IMAGE_DEFINITION_PATH . $imageDefinition->directory() . '/' . $imageDefinition::serviceName() . '.json';

        if (!$this->filesystem->has($jsonFile)) {
            $json['serviceName'] = $imageDefinition::serviceName();
            $json['width'] = $imageDefinition->width();
            $json['height'] = $imageDefinition->height();
            $json['mode'] = $imageDefinition->mode();
            $json['upscale'] = $imageDefinition->upscale();
            $json['directory'] = $imageDefinition->directory();
            $this->filesystem->write($jsonFile, \json_encode($json));
            return true;
        }
        return false;
    }

    /**
     * Checks if there are differences between the .json File and the corresponding ImageDefinition.
     *
     * Returns "true" if .json File is different to the ImageDefinition
     * Returns "false" if no differences occurred
     *
     * @param ImageDefinitionInterface $imageDefinition
     * @return bool
     */
    private function checkDefinitionChanges(ImageDefinitionInterface $imageDefinition): bool
    {
        $jsonFile = MediaPaths::PUBLIC_PATH . MediaPaths::IMAGE_DEFINITION_PATH . $imageDefinition->directory() . '/' . $imageDefinition::serviceName() . '.json';

        if ($this->filesystem->has($jsonFile)) {
            $json = $this->filesystem->read($jsonFile);
            $json = \json_decode($json, true);
            if (
                $json['width'] != $imageDefinition->width() ||
                $json['height'] != $imageDefinition->height() ||
                $json['mode'] != $imageDefinition->mode() ||
                $json['upscale'] != $imageDefinition->upscale()
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns "true" if .json File was overwritten
     * Returns "false" if no changes have been made
     *
     * @param ImageDefinitionInterface $imageDefinition
     */
    private function handleDefinitionChanges(ImageDefinitionInterface $imageDefinition): void
    {
        $jsonFile = MediaPaths::PUBLIC_PATH . MediaPaths::IMAGE_DEFINITION_PATH . $imageDefinition->directory() . '/' . $imageDefinition::serviceName() . '.json';

        $json['width'] = $imageDefinition->width();
        $json['height'] = $imageDefinition->height();
        $json['mode'] = $imageDefinition->mode();
        $json['upscale'] = $imageDefinition->upscale();

        $this->filesystem->write($jsonFile, \json_encode($json));
    }

    /**
     * Checks if there are existing Crop Parameters and evaluates them. If the existing Parameters are valid, sets the class variable.
     *
     * @param ImageDefinitionInterface $imageDefinition
     * @param Media $media
     * @return void
     */
    private function checkDefinitionCropParameters(ImageDefinitionInterface $imageDefinition, Media $media)
    {
        try {
            /** @var MediaDefinitionInfo $mediaDefinitionInfo */
            $mediaDefinitionInfo = $this->mediaDefinitionInfoRepository->findBy(['mediaId' => $media->id(), 'imageDefinition' => $imageDefinition::serviceName()])[0];
            if ($mediaDefinitionInfo->cropParameters() !== null) {
                if ($this->evaluateExistingCropParameters($media, $imageDefinition, $mediaDefinitionInfo->cropParameters())) {
                    $this->cropParameters = [
                        $imageDefinition::serviceName() => $mediaDefinitionInfo->cropParameters(),
                    ];
                }
            }
        } catch (\Exception $exception) {
            return;
        }
    }

    /**
     * Checks if existing crop Parameters are still valid.
     *
     * Returns "true" if existing Crop-Parameters are still valid
     * Returns "false" if existing Crop-Parameters are invalid
     *
     * @param MediaInterface $media
     * @param ImageDefinitionInterface $imageDefinition
     * @param array $cropParameters
     * @return bool
     */
    private function evaluateExistingCropParameters(MediaInterface $media, ImageDefinitionInterface $imageDefinition, array $cropParameters)
    {
        $xLength = $cropParameters['x2'] - $cropParameters['x1'];
        $yLength = $cropParameters['y2'] - $cropParameters['y1'];

        if ($xLength > $media->metaData()['width']) {
            return false;
        }

        if ($yLength > $media->metaData()['height']) {
            return false;
        }

        // Check Ratio
        if ($imageDefinition->width() !== null && $imageDefinition->height() !== null) {
            $cropRatio = \round($xLength / $yLength, 2);
            $definitionRatio = \round($imageDefinition->width() / $imageDefinition->height(), 2);

            if ($cropRatio !== $definitionRatio) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param ImageDefinitionInterface $imageDefinition
     * @param OutputInterface $output
     * @param SymfonyStyle $style
     * @throws \Exception
     */
    private function processImages(ImageDefinitionInterface $imageDefinition, OutputInterface $output, SymfonyStyle $style)
    {
        $medias = $this->mediaRepository->findAll();

        $progressBar = $this->customProgressBar($output, $imageDefinition, \count($medias));

        $progressBar->start();
        foreach ($medias as $media) {
            /** @var Media $media */
            if (!$this->imageHandler->isResponsible($media)) {
                continue;
            }

            try {
                if ($this->checkJsonFile($imageDefinition)) {
                    $style->writeln('Created .json File for ImageDefinition: ' . $imageDefinition::serviceName());
                }
                // If checkDefinitionChanges() returns true, evaluate existing editor generated crops
                if ($this->checkDefinitionChanges($imageDefinition)) {
                    $style->writeln('Changes have been made in ImageDefinition: ' . $imageDefinition::serviceName());
                    $this->handleDefinitionChanges($imageDefinition);
                    $style->writeln('Overwrite .json File');
                    $this->checkDefinitionCropParameters($imageDefinition, $media);
                }


                if (\array_key_exists($imageDefinition::serviceName(), $this->cropParameters)) {
                    $this->editorCommand($media, $imageDefinition, $this->cropParameters);
                }

                if (!\array_key_exists($imageDefinition::serviceName(), $this->cropParameters)) {
                    // Check if there is already and Entry with MediaId + ImageDefinition
                    $mediaDefinitionInfo = $this->mediaDefinitionInfoRepository->findOneBy(['mediaId' => $media->id(), 'imageDefinition' => $imageDefinition::serviceName()]);

                    if (!empty($mediaDefinitionInfo && $mediaDefinitionInfo->cropParameters() !== null)) {
                        // If the Entry already has CropParameters, consider them
                        $this->editorCommand($media, $imageDefinition, $mediaDefinitionInfo->cropParameters());
                    }
                    // If there is no Entry at all or an Entry without Crop Parameters, proceed normally
                    if (empty($mediaDefinitionInfo) || $mediaDefinitionInfo->cropParameters() === null) {
                        $imageHandler = $this->imageHandler->withImageDefinition($imageDefinition);
                        $imageHandler->process($media, $this->filesystem);
                    }
                }
            } catch (\Throwable $e) {
                $output->writeln('Unable to process media ' . $media->filename() . ' (' . $media->id() . ') - ' . $e->getMessage());
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $style->newLine();
        $style->writeln('Finished');
        $style->newLine(2);
    }

    /**
     * @param $media
     * @param $imageDefinition
     * @param $cropParameters
     */
    private function editorCommand($media, $imageDefinition, $cropParameters)
    {
        /** @var EditorCommand $editorCommand */
        $editorCommand = $this->commandBus->create(EditorCommand::class, []);
        $editorCommand = $editorCommand->withMedia($media);
        $editorCommand = $editorCommand->withImageDefinition($imageDefinition);
        $editorCommand = $editorCommand->withFilesystem($this->filesystem);
        $editorCommand = $editorCommand->withCropParameter($cropParameters);

        $this->commandBus->dispatch($editorCommand);
    }

    /**
     * CustomProgressBar Style
     *
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
        $progressBar->setMessage('Processing' . ' - ' . $imageDefinition::serviceName());
        return $progressBar;
    }
}
