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

use Symfony\Component\Console\Command\Command;
use KiwiSuite\Contract\Command\CommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionMapping;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionSubManager;
use Intervention\Image\ImageManager;
use KiwiSuite\Media\MediaConfig;
use KiwiSuite\Media\Repository\MediaRepository;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionInterface;
use KiwiSuite\Media\Processor\UploadImageProcessor;

final class RecreateImageDefinition extends Command implements CommandInterface
{
    /**
     * @var ImageDefinitionMapping
     */
    private $imageDefinitionMapping;

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
     * RefactorImageDefinition constructor.
     * @param ImageDefinitionMapping $imageDefinitionMapping
     * @param ImageDefinitionSubManager $imageDefinitionSubManager
     * @param MediaConfig $mediaConfig
     * @param MediaRepository $mediaRepository
     */
    public function __construct(ImageDefinitionMapping $imageDefinitionMapping, ImageDefinitionSubManager $imageDefinitionSubManager, MediaConfig $mediaConfig, MediaRepository $mediaRepository)
    {
        parent::__construct(self::getCommandName());
        $this->setDescription("Recreates all previous saved files of an ImageDefinition");
        $this->imageDefinitionMapping = $imageDefinitionMapping;
        $this->imageDefinitionSubManager= $imageDefinitionSubManager;
        $this->mediaConfig = $mediaConfig;
        $this->mediaRepository = $mediaRepository;
    }

    public function configure()
    {
        $this->addArgument(
          'name', InputArgument::OPTIONAL, 'Name of specific ImageDefinition to be refactored'
        );
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
        $this->refactor($input, $output);
        $io->newLine();
        $io->success('All Files have been refactored');
    }

    /**
     * @return string
     */
    public static function getCommandName()
    {
        return 'media:recreate-imageDefinition';
    }

    /**
     * Refactors all Images to the new given parameters of an existing ImageDefinition.
     */
    private function refactor(InputInterface $input,OutputInterface $output)
    {
        if (!empty($input->getArgument('name'))) {
            $inputName = $input->getArgument('name');
            $inputName = \trim(\ucfirst($inputName));
            if (!array_key_exists($inputName, $this->imageDefinitionMapping->getMapping())) {
                throw new Exception(\sprintf("ImageDefinition '%s' does not exist", $inputName));
            }
        }

        if (isset($inputName)) {
            $name = $this->imageDefinitionMapping->getMapping()[$inputName];
            $imageDefinition = $this->imageDefinitionSubManager->get($name);
            $count = \count($this->mediaRepository->findAll());
            $progressBar = new ProgressBar($output, $count);
            $this->generateFiles($imageDefinition, $progressBar);
        }

        if (!isset($inputName)) {
            $count = (\count($this->mediaRepository->findAll())) * (\count($this->imageDefinitionMapping->getMapping()));
            foreach ($this->imageDefinitionMapping->getMapping() as $imageDefinition) {
                $imageDefinition = $this->imageDefinitionSubManager->get($imageDefinition);
                $progressBar = new ProgressBar($output, $count);
                $this->generateFiles($imageDefinition, $progressBar);
            }
        }

        $progressBar->finish();
    }

    /**
     * @param ImageDefinitionInterface $imageDefinition
     * @param ProgressBar $progressBar
     */
    private function generateFiles(ImageDefinitionInterface $imageDefinition, ProgressBar $progressBar)
    {
        $progressBar->setFormat('verbose');
        $progressBar->start();

        $directory = \trim($imageDefinition->getDirectory(), '/');

        foreach ($this->mediaRepository->findAll() as $media) {
            $imageParameters = [
                'path'      => 'data/media/' . $media->basePath(),
                'filename'  => $media->filename(),
                'savingDir' => 'data/media/img/'. $directory . '/' . $media->basePath(),
                'width'     => $imageDefinition->getWidth(),
                'height'    => $imageDefinition->getHeight(),
                'fit'       => $imageDefinition->getFit()
            ];

            $imageProcessor = new UploadImageProcessor($imageParameters, $this->mediaConfig);
            $imageProcessor->process();

            $progressBar->setMessage($media->filename(), 'is processed');
            $progressBar->advance();
        }
    }
}
