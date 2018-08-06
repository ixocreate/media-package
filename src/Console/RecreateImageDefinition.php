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
namespace KiwiSuite\Media\Console;

use KiwiSuite\Media\Delegator\Delegators\Image;
use Symfony\Component\Console\Command\Command;
use KiwiSuite\Contract\Command\CommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionSubManager;
use Intervention\Image\ImageManager;
use KiwiSuite\Media\Config\MediaConfig;
use KiwiSuite\Media\Repository\MediaRepository;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionInterface;
use KiwiSuite\Media\Processor\UploadImageProcessor;
use KiwiSuite\Media\Exceptions\InvalidArgumentException;

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
        $this->imageDefinitionSubManager= $imageDefinitionSubManager;
        $this->mediaConfig = $mediaConfig;
        $this->mediaRepository = $mediaRepository;
        $this->imageDelegator = $imageDelegator;
    }

    public function configure()
    {
        $this
            ->addArgument('name', InputArgument::OPTIONAL, 'Name of specific ImageDefinition to be refactored')
            ->setDescription("Recreates all files or creates only missing files of an ImageDefinition")
            ->addOption('missing','m',null,'Only missing files will be created');
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
        $this->refactor($input, $output, $io);
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
     *
     * Refactors all or missing Images to the new given parameters of an existing ImageDefinition.
     */
    private function refactor(InputInterface $input,OutputInterface $output, SymfonyStyle $io)
    {
        if (!empty($input->getArgument('name'))) {
            $inputName = $input->getArgument('name');
            $inputName = \trim($inputName);
            if (!in_array(
                $inputName,
                \array_keys($this->imageDefinitionSubManager->getServiceManagerConfig()->getNamedServices()))) {
                throw new InvalidArgumentException(\sprintf("ImageDefinition '%s' does not exist", $inputName));
            }
        }

        if (isset($inputName)) {
            /** @var ImageDefinitionInterface $imageDefinition */
            $imageDefinition = $this->imageDefinitionSubManager->get($inputName);
            if ($input->getOption('missing')) {
                $this->generateFiles($imageDefinition, $output, $io, true);
                return;
            }
            $this->generateFiles($imageDefinition, $output, $io);
        }

        if (!isset($inputName)) {
            foreach ($this->imageDefinitionSubManager->getServiceManagerConfig()->getNamedServices() as $name => $imageDefinition) {
                /** @var ImageDefinitionInterface $imageDefinition */
                $imageDefinition = $this->imageDefinitionSubManager->get($imageDefinition);
                if ($input->getOption('missing')) {
                    $this->generateFiles($imageDefinition, $output, $io, true);
                    continue;
                }
                $this->generateFiles($imageDefinition, $output, $io);
            }
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
        ProgressBar::setFormatDefinition('custom','%message% -- %current%/%max% [%bar%] -- %percent:3s%%');
        $progressBar->setFormat('custom');
        $progressBar->setProgressCharacter("\xF0\x9F\x8D\xBA");
        $progressBar->setMessage('ImageDefinition: ' . $imageDefinition::serviceName());
        return $progressBar;
    }

    /**
     * @param ImageDefinitionInterface $imageDefinition
     * @param OutputInterface $output
     * @param SymfonyStyle $io
     * @param bool $missing
     * If $missing = true, only missing Images will be recreated.
     * If $missing = false, all Images will be recreated
     */
    private function generateFiles(ImageDefinitionInterface $imageDefinition, OutputInterface $output, SymfonyStyle $io, $missing = false)
    {
        $directory = \trim($imageDefinition->directory(), '/');

        if ($missing === true) {
            foreach ($this->mediaRepository->findAll() as $media) {
                $filePath = $media->basePath() . $media->filename();
                if (!file_exists(getcwd() . '/data/media/img/' . $directory . '/' . $filePath)) {
                    $mediaRepository [] = $media;
                }
            }
            if (empty($mediaRepository)) {
                $io->writeln(sprintf('There are no missing Images in ImageDefinition: %s', $imageDefinition::serviceName()));
                return;
            }
            $count = \count($mediaRepository);
            $progressBar = $this->customProgressBar($output,$imageDefinition,$count);
        }

        if ($missing === false) {
            $count = \count($this->mediaRepository->findAll());
            $mediaRepository = $this->mediaRepository->findAll();
            $progressBar = $this->customProgressBar($output, $imageDefinition, $count);
        }

        $progressBar->start();

        foreach ($mediaRepository as $media) {
            if (!$this->imageDelegator->isResponsible($media)) {
                continue;
            }
            $imageParameters = [
                'imagePath'      => 'data/media/' . $media->basePath(),
                'imageFilename'  => $media->filename(),
                'definitionSavingDir' => 'data/media/img/'. $directory . '/' . $media->basePath(),
                'definitionWidth'     => $imageDefinition->width(),
                'definitionHeight'    => $imageDefinition->height(),
                'definitionMode'      => $imageDefinition->mode(),
                'definitionUpscale'   => $imageDefinition->upscale()
            ];

            $imageProcessor = new UploadImageProcessor($imageParameters, $this->mediaConfig);
            $imageProcessor->process();
            $progressBar->advance();
        }
        $progressBar->finish();
        $io->newLine();
        $io->writeln(sprintf('Finished'));
    }
}
