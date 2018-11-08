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

use KiwiSuite\Media\Delegator\DelegatorSubManager;
use Symfony\Component\Console\Command\Command;
use KiwiSuite\Contract\Command\CommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DelegatorListCommand extends Command implements CommandInterface
{
    /**
     * @var DelegatorSubManager
     */
    private $delegatorSubManager;

    /**
     * DelegatorListCommand constructor.
     * @param DelegatorSubManager $delegatorSubManager
     */
    public function __construct(DelegatorSubManager $delegatorSubManager)
    {
        $this->delegatorSubManager = $delegatorSubManager;
        parent::__construct(self::getCommandName());
    }

    protected function configure()
    {
        $this->setDescription('A List of all registered Delegators');
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
        foreach (\array_keys($this->delegatorSubManager->getServiceManagerConfig()->getNamedServices()) as $name) {
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
