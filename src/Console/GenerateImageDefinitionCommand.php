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
    private $template = <<<'EOD'
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
        return "%s";
    }

    /**
     * @return int|null
     */
    public function width(): ?int
    {
        return null;
    }

    /**
     * @return int|null
     */
    public function height(): ?int
    {
        return null;
    }

    /**
     * @return int
     */
    public function mode(): int
    {
        return MODE;
    }
    
    /**
    * @return bool
    */
    public function upscale(): bool
    {
        return false;
    }

    /**
     * @return string
     */
    public function directory(): string
    {
        return '';
    }

}
EOD;

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
            ->setDescription('Generate a new ImageDefinition')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the Definition.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!\is_dir(\getcwd() . '/src/App/Media/ImageDefinition')) {
            \mkdir(\getcwd() . '/src/App/Media/ImageDefinition', 0777, true);
        }

        if (\file_exists(\getcwd() .
            '/src/App/Media/ImageDefinition/' .
            \trim(\ucfirst($input->getArgument('name'))) . '.php')) {
            throw new \Exception("ImageDefinition file already exists");
        }

        $this->generateFile($input);

        $output->writeln(
            \sprintf("<info>ImageDefinition '%s' generated</info>", \trim(\ucfirst($input->getArgument('name'))))
        );
    }

    /**
     * @param array $sanatizedInput
     */
    private function generateFile(InputInterface $input): void
    {
        \file_put_contents(
            \getcwd() . '/src/App/Media/ImageDefinition/' . \trim(\ucfirst($input->getArgument('name'))) . '.php',
            \sprintf($this->template,
                \trim(\ucfirst($input->getArgument('name'))),
                \trim(\ucfirst($input->getArgument('name')))
            )
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
