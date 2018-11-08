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

use KiwiSuite\Media\ImageDefinition\ImageDefinitionSubManager;
use Symfony\Component\Console\Command\Command;
use KiwiSuite\Contract\Command\CommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ImageDefinitionListCommand extends Command implements CommandInterface
{
    /**
     * @var ImageDefinitionSubManager
     */
    private $imageDefinitionSubManager;

    /**
     * ImageDefinitionListCommand constructor.
     * @param ImageDefinitionSubManager $imageDefinitionSubManager
     */
    public function __construct(ImageDefinitionSubManager $imageDefinitionSubManager)
    {
        $this->imageDefinitionSubManager = $imageDefinitionSubManager;
        parent::__construct(self::getCommandName());
    }

    protected function configure()
    {
        $this->setDescription('A List of all registered ImageDefinitions');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $data = [];
        foreach (\array_keys($this->imageDefinitionSubManager->getServiceManagerConfig()->getNamedServices()) as $name) {
            $data[] = [
                $name,
            ];
        }

        $io->table(
            ['ImageDefinition'],
            $data
        );
    }

    /**
     * @return string
     */
    public static function getCommandName()
    {
        return 'media:list-imageDefinitions';
    }
}
