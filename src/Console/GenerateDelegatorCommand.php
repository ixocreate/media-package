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

use Symfony\Component\Console\Command\Command;
use KiwiSuite\Contract\Command\CommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

final class GenerateDelegatorCommand extends Command implements CommandInterface
{
    /**
     * @var string
     */
    private $template = <<<'EOD'
<?php

declare(strict_types=1);

namespace App\Media\Delegator;

use KiwiSuite\Media\Entity\Media;
use KiwiSuite\Media\Delegator\DelegatorInterface;

final class %s implements DelegatorInterface
{
    /**
     * @var array
     */
    private $allowedMimeTypes = [];

    /**
     * @var array
     */
    private $allowedFileExtensions = [];

    public function __construct()
    {
    }

    /**
     * @return string
     */
    public static function getName() : string
    {
        return '%s';
    }

    /**
     * @param Media $media
     * @return bool
     */
    public function responsible(Media $media)
    {
        $pathInfo = \pathinfo($media->filename());
        $extension = $pathInfo['extension'];
        $responsible = true;

        if ((!\in_array($media->mimeType(), $this->allowedMimeTypes)) &&
            (!\in_array($extension, $this->allowedFileExtensions))) {
            $responsible = false;
        }
        if ($responsible === true) {
            $this->process($media);
        }
        return $responsible;
    }

    /**
     * @param Media $media
     */
    private function process(Media $media)
    {
    }
}
EOD;

    /**
     * GenerateDelegatorCommand constructor.
     */
    public function __construct()
    {
        parent::__construct(self::getCommandName());
        $this->setDescription('Generate a new Delegator');
    }

    public function configure()
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Name of Delegator');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if(!\is_dir(\getcwd() . '/src/App/Media')){
            \mkdir(\getcwd() . '/src/App/Media');
        }

        if(!\is_dir(\getcwd() . '/src/App/Media/Delegator')){
            \mkdir(\getcwd() . '/src/App/Media/Delegator');
        }

        if (\file_exists(\getcwd() .
            '/src/App/Media/Delegator/' .
            \trim(\ucfirst($input->getArgument('name'))) . '.php')) {
            throw new \Exception("Delegator file already exists");
        }

        $this->generateFile($input);

        $output->writeln(
            \sprintf("<info>Delegator '%s' generated</info>", \trim(\ucfirst($input->getArgument('name'))))
        );
    }

    /**
     * @param array $sanatizedInput
     */
    private function generateFile(InputInterface $input): void
    {
        \file_put_contents(
            \getcwd() . '/src/App/Media/Delegator/' . \trim(\ucfirst($input->getArgument('name'))) . '.php',
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
        return "media:generate-delegator";
    }

}