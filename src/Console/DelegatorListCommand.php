<?php/**
 * kiwi-suite/media (https://github.com/kiwi-suite/media)
 *
 * @package kiwi-suite/media
 * @see https://github.com/kiwi-suite/media
 * @copyright Copyright (c) 2010 - 2018 kiwi suite GmbH
 * @license MIT License
 */
declare(strict_types=1);

use Symfony\Component\Console\Command\Command;
use KiwiSuite\Contract\Command\CommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use KiwiSuite\Media\Delegator\DelegatorMapping;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DelegatorListCommand extends Command implements CommandInterface
{
    /**
     * @var DelegatorMapping
     */
    private $delegatorMapping;

    /**
     * DelegatorListCommand constructor.
     * @param DelegatorMapping $delegatorMapping
     */
    public function __construct(DelegatorMapping $delegatorMapping)
    {
        $this->delegatorMapping = $delegatorMapping;
        parent::__construct(self::getCommandName());
        $this->setDescription('A List of all registered Delegators');
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
        foreach ($this->delegatorMapping->getMapping() as $name => $namespace) {
            $data[] = [
                $name,
            ];
        }

        $io->table(
            ['Delegator'],
            $data
        );
    }

    /**
     * @return string
     */
    public static function getCommandName()
    {
        return 'media:list-delegators';
    }
}