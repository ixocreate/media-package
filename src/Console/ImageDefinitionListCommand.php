<?php
declare(strict_types=1);

use Symfony\Component\Console\Command\Command;
use KiwiSuite\Contract\Command\CommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionMapping;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ImageDefinitionListCommand extends Command implements CommandInterface
{
    /**
     * @var ImageDefinitionMapping
     */
    private $imageDefinitionMapping;

    /**
     * ImageDefinitionListCommand constructor.
     * @param ImageDefinitionMapping $imageDefinitionMapping
     */
    public function __construct(ImageDefinitionMapping $imageDefinitionMapping)
    {
        $this->imageDefinitionMapping = $imageDefinitionMapping;
        parent::__construct(self::getCommandName());
        $this->setDescription('A List of all registered ImageDefinitions');

    }

    protected function configure()
    {
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
        foreach ($this->imageDefinitionMapping->getMapping() as $name => $namespace) {
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
        return 'media:list-ImageDefinitions';
    }
}