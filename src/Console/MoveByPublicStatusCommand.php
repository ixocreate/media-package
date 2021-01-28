<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Console;

use Ixocreate\Application\Console\CommandInterface;
use Ixocreate\CommandBus\CommandBus;
use Ixocreate\Media\Command\Media\UpdateCommand;
use Ixocreate\Media\Repository\MediaRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class MoveByPublicStatusCommand extends Command implements CommandInterface
{
    /**
     * @var MediaRepository
     */
    private $mediaRepository;

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * RefactorImageDefinition constructor.
     *
     * @param MediaRepository $mediaRepository
     * @param CommandBus $commandBus
     */
    public function __construct(
        MediaRepository $mediaRepository,
        CommandBus $commandBus
    ) {
        parent::__construct(self::getCommandName());
        $this->mediaRepository = $mediaRepository;
        $this->commandBus = $commandBus;
    }

    public function configure()
    {
        $this->setDescription('Moves all media files by public status');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var UpdateCommand $command */
        $command = $this->commandBus->create(UpdateCommand::class, []);

        $mediaFiles = $this->mediaRepository->findAll();

        foreach ($mediaFiles as $mediaFile) {
            $command = $command->withMedia($mediaFile);
            $command = $command->withPublicStatus($mediaFile->publicStatus());
            $this->commandBus->dispatch($command);
        }

        return 0;
    }

    /**
     * @return string
     */
    public static function getCommandName()
    {
        return 'media:move-by-public-status';
    }
}
