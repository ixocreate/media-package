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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionInterface;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionSubManager;

final class GenerateImageDefinitionCommand extends Command implements CommandInterface
{
    /**
     * @var string
     */
    private $phpTemplate = <<<'EOD'
<?php

declare(strict_types=1);

namespace App\Media\ImageDefinition;

use KiwiSuite\Media\ImageDefinition\ImageDefinitionInterface;

final class %s implements ImageDefinitionInterface
{
    /**
     * @return string
     */
    public static function serviceName(): string
    {
        return '%s';
    }

    /**
     * @return int|null
     */
    public function width(): ?int
    {
        return %s;
    }

    /**
     * @return int|null
     */
    public function height(): ?int
    {
        return %s;
    }

    /**
     * @return string
     */
    public function mode(): string
    {
        return '%s';
    }
    
    /**
    * @return bool
    */
    public function upscale(): bool
    {
        return %s;
    }

    /**
     * @return string
     */
    public function directory(): string
    {
        return '%s';
    }

}
EOD;

    /**
     * @var string
     */
    private $definitionPath = '/src/App/Media/ImageDefinition/';

    /**
     * @var string
     */
    private $imagePath = '/data/media/img/';

    /**
     * @var array
     */
    private $allowedModes = ['fit', 'fitCrop', 'canvas', 'canvasFitCrop'];

    /**
     * @var array
     */
    private $allowedUpscale = ['true', 'false'];

    /**
     * GenerateImageDefinitionCommand constructor.
     */
    public function __construct()
    {
        parent::__construct(self::getCommandName());
    }

    public function configure()
    {
        $this
            ->setDescription('Generate a new ImageDefinition and a Reference')
            ->addArgument('serviceName', InputArgument::REQUIRED, 'Name of the Definition.')
            ->addArgument('width', InputArgument::OPTIONAL, 'Width of ImageDefinition')
            ->addArgument('height', InputArgument::OPTIONAL, 'Height of ImageDefinition')
            ->addArgument('mode', InputArgument::OPTIONAL,
                'Mode of ImageDefinition, allowed modes are: fit, fitCrop, canvas, canvasFitCrop','fit')
            ->addArgument('upscale', InputArgument::OPTIONAL, 'Allow Upscale?','false');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!in_array($input->getArgument('mode'), $this->allowedModes)) {
            throw new \Exception('Given Mode ist not supported');
        }

        if (!in_array($input->getArgument('upscale'), $this->allowedUpscale)) {
            throw new \Exception('Upscale must be declared true or false');
        }

        if (!\is_dir(\getcwd() . $this->definitionPath)) {
            \mkdir(\getcwd() . $this->definitionPath, 0777, true);
        }

        if (\file_exists(\getcwd() .
            $this->definitionPath .
            \trim($input->getArgument('serviceName')) . '.php')) {
            throw new \Exception("ImageDefinition file already exists");
        }

        if(!\is_dir(getcwd() . $this->imagePath . $this->CamelCaseToDashSeparated($input->getArgument('serviceName')))) {
            \mkdir(getcwd() . $this->imagePath . $this->CamelCaseToDashSeparated($input->getArgument('serviceName')),0777, true);
        }


        $this->generateFiles($input);

        $output->writeln(
            \sprintf("<info>ImageDefinition '%s' generated</info>", \trim($input->getArgument('serviceName')))
        );
    }

    /**
     * @param string $name
     */
    private function generateFiles(InputInterface $input): void
    {
        $clearedInput = $this->clearInput($input);

        $this->generatePHP($clearedInput);
        $this->generateJson($clearedInput);
    }

    /**
     * @param string $name
     * @return string
     */
    private function CamelCaseToDashSeparated(string $name)
    {
        return \strtolower(\preg_replace('%([a-z])([A-Z])%', '\1-\2', $name));
    }

    /**
     * @param InputInterface $input
     * @return array
     */
    private function clearInput(InputInterface $input)
    {
        $clearedInput = [];
        $clearedInput['serviceName'] = \trim($input->getArgument('serviceName'));

        $width = $input->getArgument('width');
        settype($width, 'integer');
        if($width === 0){$width = null;}
        $height = $input->getArgument('height');
        settype($height,'integer');
        if($height === 0){$height = null;}
        $clearedInput['width'] = $width;
        $clearedInput['height'] = $height;
        $clearedInput['mode'] = $input->getArgument('mode');
        $clearedInput['upscale'] = $input->getArgument('upscale');

        return $clearedInput;
    }

    /**
     * @param string $name
     */
    private function generatePHP($clearedInput)
    {
        $width = $clearedInput['width'];
        if ($width === null) {$width = 'null';}
        $height = $clearedInput['height'];
        if ($height === null) {$height = 'null';}
        \file_put_contents(
            \getcwd() . $this->definitionPath . \ucfirst($clearedInput['serviceName']) . '.php',
            \sprintf($this->phpTemplate,
                \ucfirst($clearedInput['serviceName']),
                \trim($this->CamelCaseToDashSeparated($clearedInput['serviceName'])),
                $width,
                $height,
                $clearedInput['mode'],
                $clearedInput['upscale'],
                $this->CamelCaseToDashSeparated($clearedInput['serviceName'])
            )
        );
    }

    /**
     * @param string $name
     */
    private function generateJson($clearedInput)
    {
        $data = [
            'serviceName' => \trim($this->CamelCaseToDashSeparated($clearedInput['serviceName'])),
            'width' => $clearedInput['width'],
            'height' => $clearedInput['height'],
            'mode'  => $clearedInput['mode'],
            'upscale' => $clearedInput['upscale'],
            'directory' => $this->CamelCaseToDashSeparated($clearedInput['serviceName'])
        ];
        $jsonEncode = json_encode($data);

        \file_put_contents(\getcwd() . $this->imagePath . '/' .$this->CamelCaseToDashSeparated($clearedInput['serviceName'])
            . '/' . \trim($this->CamelCaseToDashSeparated($clearedInput['serviceName'])) . '.json',
        $jsonEncode
        );
    }

    /**
     * @return string
     */
    public static function getCommandName()
    {
        return "media:generate-imageDefinition";
    }
}
