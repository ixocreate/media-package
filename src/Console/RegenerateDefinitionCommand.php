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
use Ixocreate\Media\Config\MediaPaths;
use Ixocreate\Media\Entity\Media;
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
     * @param ImageHandler $imageHandler
     * @param FilesystemManager $filesystemManager
     * @param MediaRepository $mediaRepository
     * @param ImageDefinitionSubManager $imageDefinitionSubManager
     * @param MediaDefinitionInfoRepository $mediaDefinitionInfoRepository
     * @param CommandBus $commandBus
     */
    public function __construct(
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
            ->setHelp('After adding or changing an ImageDefinition use this command to update the files')
            ->setDefinition(
                new InputDefinition([
                    new InputArgument('name', InputArgument::OPTIONAL, 'Name of specific ImageDefinition to be regenerated'),
                    new InputOption('all', 'a', null, 'All Definitions will be regenerated'),
                    new InputOption('changed', 'c', null, 'Only files of new or changed ImageDefinitions will be (re)generated'),
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

        $limit = \str_ireplace(['G', 'M', 'K'], ['000000000', '000000', '000'], \ini_get('memory_limit'));
        if ($limit < 256000000) {
            \ini_set('memory_limit', '256M');
            $style->info('Increase memory limit to 256M');
        }

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
            return 0;
        }

        if ($input->getOption('changed') && $input->getOption('all')) {
            $style->error('You can not use option "--changed" with option "--all"');
            return 1;
        }

        // In Case all Definitions should be checked
        if ($input->getOption('all')) {
            $this->runAll($output, $style);
            return 0;
        }

        $this->runNewOrChanged($output, $style);

        return 0;
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
    private function runNewOrChanged(OutputInterface $output, SymfonyStyle $style)
    {
        $changedDefinitions = [];
        foreach ($this->imageDefinitionSubManager->services() as $imageDefinitionClassName) {
            $imageDefinition = $this->imageDefinitionSubManager->get($imageDefinitionClassName);

            if ($this->checkDefinitionChanges($imageDefinition, true)) {
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
     * Checks if there are differences between the .json File and the corresponding ImageDefinition.
     *
     * Returns "true" if .json File is different to the ImageDefinition
     * Returns "false" if no differences occurred
     *
     * @param ImageDefinitionInterface $imageDefinition
     * @param bool $missingFileIsChange
     * @return bool
     */
    private function checkDefinitionChanges(ImageDefinitionInterface $imageDefinition, bool $missingFileIsChange = false): bool
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
            return false;
        }
        return $missingFileIsChange;
    }

    /**
     * Saves json file for an ImageDefinition
     *
     * @param ImageDefinitionInterface $imageDefinition
     * @param bool $force
     */
    private function writeJsonFile(ImageDefinitionInterface $imageDefinition, bool $force = false): bool
    {
        $jsonFile = MediaPaths::PUBLIC_PATH . MediaPaths::IMAGE_DEFINITION_PATH . $imageDefinition->directory() . '/' . $imageDefinition::serviceName() . '.json';

        if ($this->filesystem->has($jsonFile) && !$force) {
            return false;
        }

        $json['serviceName'] = $imageDefinition::serviceName();
        $json['width'] = $imageDefinition->width();
        $json['height'] = $imageDefinition->height();
        $json['mode'] = $imageDefinition->mode();
        $json['upscale'] = $imageDefinition->upscale();
        $json['directory'] = $imageDefinition->directory();
        $this->filesystem->write($jsonFile, \json_encode($json, JSON_PRETTY_PRINT));

        return true;
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
    private function evaluateExistingCropParameters(MediaInterface $media, ImageDefinitionInterface $imageDefinition, array $cropParameters): bool
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

        $definitionHasChanges = $this->checkDefinitionChanges($imageDefinition);
        if ($this->writeJsonFile($imageDefinition, $definitionHasChanges)) {
            $style->writeln('Created or updated .json file for ImageDefinition: ' . $imageDefinition::serviceName());
        }

        $progressBar = $this->customProgressBar($output, $imageDefinition, \count($medias));

        $progressBar->start();
        foreach ($medias as $media) {
            /** @var Media $media */
            if (!$this->imageHandler->isResponsible($media)) {
                continue;
            }

            if ((string)$media->id() !== 'a3ad9c72-4f01-4132-9838-640aa3ced5d9') {
                continue;
            }

            try {
                // Check if there is already and Entry with MediaId + ImageDefinition
                $mediaDefinitionInfo = $this->mediaDefinitionInfoRepository->findOneBy(['mediaId' => $media->id(), 'imageDefinition' => $imageDefinition::serviceName()]);

                if (!empty($mediaDefinitionInfo && $mediaDefinitionInfo->cropParameters() !== null)) {
                    // If checkDefinitionChanges() returns true, evaluate existing editor generated crops
                    if (!$definitionHasChanges || $this->evaluateExistingCropParameters($media, $imageDefinition, $mediaDefinitionInfo->cropParameters())) {
                        // If the Entry already has CropParameters, consider them
                        $this->editorCommand($media, $imageDefinition, $mediaDefinitionInfo->cropParameters());

                        $progressBar->advance();
                        continue;
                    }
                }

                $imageHandler = $this->imageHandler->withImageDefinition($imageDefinition);
                $imageHandler->process($media, $this->filesystem);
            } catch (\Throwable $e) {
                $output->writeln('Unable to process media ' . $media->filename() . ' (' . $media->id() . ') - ' . $e->getMessage());
                \var_dump($e);
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
