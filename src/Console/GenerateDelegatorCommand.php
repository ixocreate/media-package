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
/**
 * kiwi-suite/media (https://github.com/kiwi-suite/media)
 *
 * @package kiwi-suite/media
 * @see https://github.com/kiwi-suite/media
 * @copyright Copyright (c) 2010 - 2018 kiwi suite GmbH
 * @license MIT License
 */
declare(strict_types=1);

namespace KiwiSuite\Media\Delegator\Delegators;

use KiwiSuite\Config\Config;
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

    public function __construct()
    {
        parent::__construct(self::getCommandName());
        $this->setDescription('Generate a new Delegator');
    }

    public function configure()
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Name of Delegator');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (\file_exists(\getcwd() . '/vendor/kiwi-suite/media/src/Delegator/Delegators/' . \trim(\ucfirst($input->getArgument('name'))) . '.php')) {
            throw new \Exception("Delegator file already exists");
        }
        $this->generateFile($input);

        $output->writeln(\sprintf("<info>Delegator '%s' generated</info>", \trim(\ucfirst($input->getArgument('name')))));
    }

    /**
     * @param array $sanatizedInput
     */
    private function generateFile(InputInterface $input): void
    {
        \file_put_contents(
            \getcwd() . '/vendor/kiwi-suite/media/src/Delegator/Delegators/' . \trim(\ucfirst($input->getArgument('name'))) . '.php',
            \sprintf($this->template,
                \trim(\ucfirst($input->getArgument('name'))),
                \trim(\ucfirst($input->getArgument('name')))
            )
        );
    }

    public static function getCommandName()
    {
        return "media:generate-Delegator";
    }

    // TODO : Maybe auto-register generated Delegators
}