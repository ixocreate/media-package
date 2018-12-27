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

namespace Ixocreate\Media\Console;

use Ixocreate\Media\Delegator\Delegators\Image;
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
use Ixocreate\Media\ImageDefinition\ImageDefinitionInterface;
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
     * @var string
     */
    private $publicImagePath = '/data/media/img/';

    /**
     * @var string
     */
    private $privateImagePath = '/data/media_private/img/';


    /**
     * RefactorImageDefinition constructor.
     * @param ImageDefinitionSubManager $imageDefinitionSubManager
     * @param MediaConfig $mediaConfig
     * @param MediaRepository $mediaRepository
     * @param Image $imageDelegator
     */
    public function __construct(
        ImageDefinitionSubManager $imageDefinitionSubManager,
        MediaConfig $mediaConfig,
        MediaRepository $mediaRepository,
        Image $imageDelegator
    )
    {
        parent::__construct(self::getCommandName());
        $this->imageDefinitionSubManager = $imageDefinitionSubManager;
        $this->mediaConfig = $mediaConfig;
        $this->mediaRepository = $mediaRepository;
        $this->imageDelegator = $imageDelegator;
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
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Refactor ImageDefinition');
        $this->processInput($input, $output, $io);
    }

    /**
     * @return string
     */
    public static function getCommandName()
    {
        return 'media:recreate-imageDefinition';
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param SymfonyStyle $io
     */
    private function processInput(InputInterface $input, OutputInterface $output, SymfonyStyle $io)
    {
        if ($input->getOption('all') && empty($input->getArgument('name'))) {
            foreach ($this->imageDefinitionSubManager->getServiceManagerConfig()->getNamedServices() as $name => $imageDefinition) {
                /** @var ImageDefinitionInterface $imageDefinition */
                $imageDefinition = $this->imageDefinitionSubManager->get($imageDefinition);
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
     */
    private function generateFiles(ImageDefinitionInterface $imageDefinition, InputInterface $input, OutputInterface $output, SymfonyStyle $io)
    {
        if ($input->getOption('missing')) {
            $mediaRepository = $this->handleMissing($imageDefinition, $input, $io);
            if (empty($mediaRepository)) {
                $io->writeln(\sprintf('There are no missing Images in ImageDefinition: %s', $imageDefinition::serviceName()));
                return;
            }
            $count = \count($mediaRepository);
            $progressBar = $this->customProgressBar($output, $imageDefinition, $count);
            if ($input->getOption('changed')) {
                $this->handleChanges($imageDefinition, $mediaRepository, $io, $progressBar);
                return;
            }
        }

        if (!$input->getOption('missing')) {
            $mediaRepository = $this->mediaRepository->findAll();
            $count = \count($this->mediaRepository->findAll());
            $progressBar = $this->customProgressBar($output, $imageDefinition, $count);
            if ($input->getOption('changed')) {
                $this->handleChanges($imageDefinition, $mediaRepository, $io, $progressBar);
                return;
            }
        }

        return $this->processImages($imageDefinition, $mediaRepository, $io, $progressBar);
    }

    /**
     * @param ImageDefinitionInterface $imageDefinition
     * @param InputInterface $input
     * @param SymfonyStyle $io
     * @return array
     */
    private function handleMissing(ImageDefinitionInterface $imageDefinition, InputInterface $input, SymfonyStyle $io)
    {
        $mediaRepository = [];
        foreach ($this->mediaRepository->findAll() as $media) {
            $filePath = $media->basePath() . $media->filename();
            if (
                !\file_exists(\getcwd() . $this->publicImagePath . $imageDefinition->directory() . '/' . $filePath) &&
                !\file_exists(\getcwd() . $this->privateImagePath . $imageDefinition->directory() . '/' . $filePath)
            ) {
                if (!$this->imageDelegator->isResponsible($media)) {
                    continue;
                }
                $mediaRepository [] = $media;
            }
        }
        return $mediaRepository;
    }

    /**
     * @param ImageDefinitionInterface $imageDefinition
     * @param $mediaRepository
     * @param SymfonyStyle $io
     * @param ProgressBar $progressBar
     */
    private function handleChanges(ImageDefinitionInterface $imageDefinition, $mediaRepository, SymfonyStyle $io, ProgressBar $progressBar)
    {
        $jsonFiles = [
            'publicJsonFile' => \getcwd() . $this->publicImagePath . $imageDefinition->directory() . '/' . $imageDefinition->directory() . '.json',
            'privateJsonFile' => \getcwd() . $this->privateImagePath . $imageDefinition->directory() . '/' . $imageDefinition->directory() . '.json'
        ];


        foreach ($jsonFiles as $type => $file) {
            if (\file_exists($file)) {
                $content = \file_get_contents($file);
                $json = \json_decode($content, true);

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
                    $newJson = \json_encode($json);
                    \file_put_contents($file, $newJson);
                    return $this->processImages($imageDefinition, $mediaRepository, $io, $progressBar);
                }
            }

            if (!\file_exists($file)) {
                $json['serviceName'] = $imageDefinition::serviceName();
                $json['width'] = $imageDefinition->width();
                $json['height'] = $imageDefinition->height();
                $json['mode'] = $imageDefinition->mode();
                $json['upscale'] = $imageDefinition->upscale();
                $json['directory'] = $imageDefinition->directory();
                $newJson = \json_encode($json);
                \file_put_contents($file, $newJson);
                return $this->processImages($imageDefinition, $mediaRepository, $io, $progressBar);
            }
        }

        return $io->writeln('No changes have been made in ImageDefinition: ' . $imageDefinition::serviceName());
    }

    /**
     * @param ImageDefinitionInterface $imageDefinition
     * @param $mediaRepository
     * @param SymfonyStyle $io
     * @param ProgressBar $progressBar
     */
    private function processImages(ImageDefinitionInterface $imageDefinition, $mediaRepository, SymfonyStyle $io, ProgressBar $progressBar)
    {
        $progressBar->start();

        foreach ($mediaRepository as $media) {
            if (!$this->imageDelegator->isResponsible($media)) {
                continue;
            }

            $imageProcessor = new ImageProcessor($media, $imageDefinition, $this->mediaConfig);
            $imageProcessor->process();
            $progressBar->advance();
        }
        $progressBar->finish();
        $io->newLine();
        $io->writeln(\sprintf('Finished'));
    }
}
