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
     * RefactorImageDefinition constructor.
     * @param ImageDefinitionMapping $imageDefinitionMapping
     * @param ImageDefinitionSubManager $imageDefinitionSubManager
     * @param MediaConfig $mediaConfig
     * @param MediaRepository $mediaRepository
     */
    public function __construct(ImageDefinitionSubManager $imageDefinitionSubManager,
                                MediaConfig $mediaConfig,
                                MediaRepository $mediaRepository
    )
    {
        parent::__construct(self::getCommandName());
        $this->imageDefinitionSubManager= $imageDefinitionSubManager;
        $this->mediaConfig = $mediaConfig;
        $this->mediaRepository = $mediaRepository;
    }

    public function configure()
    {
        $this
            ->addArgument('name', InputArgument::OPTIONAL, 'Name of specific ImageDefinition to be refactored')
            ->setDescription("Recreates all previous saved files of an ImageDefinition");
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
            $count = \count($this->mediaRepository->findAll());
            $progressBar = new ProgressBar($output, $count);
            $this->generateFiles($imageDefinition, $progressBar);
        }

        if (!isset($inputName)) {
            $count =
                (\count($this->mediaRepository->findAll())) *
                (\count($this->imageDefinitionSubManager->getServices()));

            foreach ($this->imageDefinitionSubManager->getServiceManagerConfig()->getNamedServices() as $name => $imageDefinition) {
                /** @var ImageDefinitionInterface $imageDefinition */
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

        $directory = \trim($imageDefinition->directory(), '/');

        foreach ($this->mediaRepository->findAll() as $media) {
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

            $progressBar->setMessage($media->filename(), 'is processed');
            $progressBar->advance();
        }
    }
}
