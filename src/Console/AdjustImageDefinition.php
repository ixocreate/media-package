<?php
declare(strict_types=1);

use Symfony\Component\Console\Command\Command;
use KiwiSuite\Contract\Command\CommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class AdjustImageDefinition extends Command implements CommandInterface
{
    private $commandBus;

    public function __construct()
    {
        parent::__construct(self::getCommandName());
        $this->setDescription("Adjust's an existing ImageDefinition and refactores all previous saved files");
    }

    public function configure()
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Hahahaha! Not working by now!');
    }

    public static function getCommandName()
    {
        return 'media:adjust-imageDefinition';
    }

    private function generateFile()
    {
    }
}