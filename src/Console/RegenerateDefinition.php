<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Console;

use Ixocreate\Application\Console\CommandInterface;
use Ixocreate\Filesystem\FilesystemInterface;
use Ixocreate\Filesystem\FilesystemManager;
use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\Entity\MediaDefinitionInfo;
use Ixocreate\Media\Exception\InvalidConfigException;
use Ixocreate\Media\Handler\ImageHandler;
use Ixocreate\Media\ImageDefinition\ImageDefinitionSubManager;
use Ixocreate\Media\ImageDefinitionInterface;
use Ixocreate\Media\MediaInterface;
use Ixocreate\Media\MediaPaths;
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

final class RegenerateDefinition extends Command implements CommandInterface
{

    /**
     * @var array | null
     */
    private $cropParameters;

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
     * RegenerateDefinition constructor.
     *
     * @param MediaConfig $mediaConfig
     * @param ImageHandler $imageHandler
     * @param FilesystemManager $filesystemManager
     * @param MediaRepository $mediaRepository
     * @param ImageDefinitionSubManager $imageDefinitionSubManager
     * @param MediaDefinitionInfoRepository $mediaDefinitionInfoRepository
     */
    public function __construct(
        MediaConfig $mediaConfig,
        ImageHandler $imageHandler,
        FilesystemManager $filesystemManager,
        MediaRepository $mediaRepository,
        ImageDefinitionSubManager $imageDefinitionSubManager,
        MediaDefinitionInfoRepository $mediaDefinitionInfoRepository
    ) {
        parent::__construct(self::getCommandName());
        $this->imageDefinitionSubManager = $imageDefinitionSubManager;
        $this->mediaDefinitionInfoRepository = $mediaDefinitionInfoRepository;
        $this->mediaConfig = $mediaConfig;
        $this->mediaRepository = $mediaRepository;
        $this->filesystemManager = $filesystemManager;
        $this->imageHandler = $imageHandler;
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
            ->setDescription('Regenerates or generates ImageDefinition related Files')
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
     * @return int|void|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->filesystemManager->has('media')) {
            throw new InvalidConfigException('Storage Config not set');
        }

        $this->filesystem = $this->filesystemManager->get('media');

        $style = new SymfonyStyle($input, $output);
        $style->title('Regenerate Definition');

        $this->evaluateInput($input, $output, $style);
    }

    /**
     * Evaluates the Command-Input
     *
     * If Input is incorrect, returns Console-Note
     * If Input it valid, passes to the responsible method
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param SymfonyStyle $style
     * @throws \Exception
     */
    private function evaluateInput(InputInterface $input, OutputInterface $output, SymfonyStyle $style)
    {
        if (!$input->getOption('all') && !$input->getArgument('name')) {
            $style->note('Please enter a valid Option or specify a valid ImageDefinition "name"');
        }

        if ($input->getOption('all') && $input->getArgument('name')) {
            $style->note('You can only use Option "--all" or specify a valid ImageDefinition "name"');
        }

        if ($input->getOption('changed') && $input->getArgument('name')) {
            $style->note('You can only use Option "--changed" or specify a valid ImageDefinition "name"');
        }

        // In Case changed & all were given, run all
        if ($input->getOption('changed') && $input->getArgument('all')) {
            $this->runAll($input, $output, $style);
        }

        // In Case all Definitions should be checked
        if ($input->getOption('all')) {
            $this->runAll($input, $output, $style);
        }

        // In Case a specific Definition should be checked
        if ($input->getArgument('name')) {
            $this->runSpecific($input, $output, $style);
        }

        // In Case only changed Definitions should be checked
        if ($input->getOption('changed')) {
            $this->runChanged($input, $output, $style);
        }
    }

    /**
     * Method is used if the options "all" was selected
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param SymfonyStyle $style
     * @throws \Exception
     */
    private function runAll(InputInterface $input, OutputInterface $output, SymfonyStyle $style)
    {
        foreach ($this->mediaRepository->findAll() as $media) {
            /** @var MediaInterface $media */
            if (!$this->imageHandler->isResponsible($media)) {
                continue;
            }

            foreach ($this->imageDefinitionSubManager->getServices() as $imageDefinitionClassName) {
                $imageDefinition = $this->imageDefinitionSubManager->get($imageDefinitionClassName);

                if ($this->checkJsonFile($imageDefinition)) {
                    $style->writeln('Created .json File for ImageDefinition: ' . $imageDefinition::serviceName());
                }
                // If checkDefinitionChanges() returns true, evaluate existing editor generated crops
                if ($this->checkDefinitionChanges($imageDefinition)) {
                    $style->writeln('Changes have been made in ImageDefinition: ' . $imageDefinition::serviceName());
                    $this->checkDefinitionCropParameters($imageDefinition, $media);
                } else {
                    $style->writeln('No changes have been made in ImageDefinition: ' . $imageDefinition::serviceName());
                }

                $this->processImages($imageDefinition, $input, $output, $style, $media, true);
            }
        }
    }

    /**
     * Method is used if the argument "name" was filled
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param SymfonyStyle $style
     */
    private function runSpecific(InputInterface $input, OutputInterface $output, SymfonyStyle $style)
    {
        $inputName = \trim($input->getArgument('name'));
        $inputName = \strtolower($inputName);
        if (!\in_array(
            $inputName,
            \array_keys($this->imageDefinitionSubManager->getServiceManagerConfig()->getNamedServices())
        )) {
            $style->error(\sprintf("ImageDefinition '%s' does not exist", $inputName));
            return;
        }
        $imageDefinition = $this->imageDefinitionSubManager->get($inputName);
        $this->checkJsonFile($imageDefinition);
        $this->checkDefinitionChanges($imageDefinition);
    }

    private function runChanged(InputInterface $input, OutputInterface $output, SymfonyStyle $style) {
        // TODO: implement
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
        $jsonFile = MediaPaths::PUBLIC_PATH . MediaPaths::IMAGE_DEFINITION_PATH .$imageDefinition->directory() . '/' . $imageDefinition::serviceName() . '.json';

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
     * If there are differences, .json File will be overwritten with the current ImageDefinition credentials.
     *
     * Returns "true" if .json File was overwritten
     * Returns "false" if no changes have been made
     *
     * @param ImageDefinitionInterface $imageDefinition
     * @return bool
     */
    private function checkDefinitionChanges(ImageDefinitionInterface $imageDefinition): bool
    {
        $jsonFile = MediaPaths::PUBLIC_PATH . MediaPaths::IMAGE_DEFINITION_PATH .$imageDefinition->directory() . '/' . $imageDefinition::serviceName() . '.json';

//        if ($this->filesystem->has($jsonFile)) {
//            $json = $this->filesystem->read($jsonFile);
//            $json = \json_decode($json, true);
//            if (
//                $json['width'] != $imageDefinition->width() ||
//                $json['height'] != $imageDefinition->height() ||
//                $json['mode'] != $imageDefinition->mode() ||
//                $json['upscale'] != $imageDefinition->upscale()
//            ) {
//                $json['width'] = $imageDefinition->width();
//                $json['height'] = $imageDefinition->height();
//                $json['mode'] = $imageDefinition->mode();
//                $json['upscale'] = $imageDefinition->upscale();
//                $this->filesystem->put($jsonFile, \json_encode($json));
//                return true;
//            }
//        }
//        return false;
        return true;
    }

    /**
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
                    $this->cropParameters = $mediaDefinitionInfo->cropParameters();
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
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param SymfonyStyle $style
     * @param Media $media
     * @throws \Exception
     */
    private function processImages(ImageDefinitionInterface $imageDefinition, InputInterface $input, OutputInterface $output, SymfonyStyle $style, Media $media, $all = false)
    {
        if ($all === false) {
            $this->imageHandler->withImageDefinition($imageDefinition)->process($media, $this->filesystem);
        }

        if ($all === true) {
            $this->imageHandler->process($media, $this->filesystem);
        }

        if ($this->cropParameters !== null) {
            $mediaDefinition = $this->mediaDefinitionInfoRepository->find(['mediaId' => $media->id(), 'imageDefinition' => $imageDefinition::serviceName()]);
            $mediaDefinition = $mediaDefinition->with('cropParameters', $this->cropParameters);
            $this->mediaDefinitionInfoRepository->save($mediaDefinition);
        }

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
        $progressBar->setMessage('ImageDefinition: ' . $imageDefinition::serviceName());
        return $progressBar;
    }
}