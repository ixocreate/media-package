<?php
/**
 * @see https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Console;

use Ixocreate\Media\ImageDefinition\ImageDefinitionSubManager;
use Symfony\Component\Console\Command\Command;
use Ixocreate\Contract\Command\CommandInterface;
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
