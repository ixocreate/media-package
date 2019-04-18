<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Console;

use Ixocreate\Media\ImageDefinition\ImageDefinitionInterface;
use Symfony\Component\Console\Command\Command;
use Ixocreate\Application\Console\CommandInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ixocreate\Media\ImageDefinition\ImageDefinitionSubManager;

final class DisplayImageDefinition extends Command implements CommandInterface
{
    /*
     * @var ImageDefinitionSubManager
     */
    private $imageDefinitionSubManager;

    /**
     * RefactorImageDefinition constructor.
     * @param ImageDefinitionSubManager $imageDefinitionSubManager
     */
    public function __construct(
        ImageDefinitionSubManager $imageDefinitionSubManager
    ) {
        parent::__construct(self::getCommandName());
        $this->imageDefinitionSubManager = $imageDefinitionSubManager;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rows = [];

        foreach ($this->imageDefinitionSubManager->getServices() as $service) {
            /** @var ImageDefinitionInterface $service */
            $service = $this->imageDefinitionSubManager->get($service);

            $rows[] = [
                $service::serviceName(),
                (empty($service->width())) ? '-' : $service->width(),
                (empty($service->height())) ? '-' : $service->height(),
                $service->mode(),
                ($service->upscale() === true) ? 'Yes' : 'No',
                $service->directory(),
            ];
        }
        \uasort($rows, function ($item1, $item2) {
            return \strcmp($item1[0], $item2[0]);
        });
        $table = new Table($output);
        $table
            ->setHeaders(['Name', 'Width', 'Height', 'Mode', 'Upscale', 'directory'])
            ->setRows($rows)
        ;
        $table->render();
    }

    /**
     * @return string
     */
    public static function getCommandName()
    {
        return 'media:display-image-definition';
    }
}
