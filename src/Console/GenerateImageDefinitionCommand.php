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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionInterface;
use KiwiSuite\Media\ImageDefinition\ImageDefinitionSubManager;

final class GenerateImageDefinitionCommand extends Command implements CommandInterface
{
    /**
     * @var string
     */
    private $template = <<<'EOD'
<?php
/**
 * kiwi-suite/%s (https://github.com/kiwi-suite/%s)
 *
 * @package kiwi-suite/%s
 * @see https://github.com/kiwi-suite/%s
 * @copyright Copyright (c) 2010 - 2018 kiwi suite GmbH
 * @license MIT License
 */
declare(strict_types=1);

namespace KiwiSuite\%s\ImageDefinition;

use KiwiSuite\Media\ImageDefinition\ImageDefinitionInterface;

final class %s implements ImageDefinitionInterface
{
    /**
     * @var int
     */
    private $width = null;

    /**
     * @var int
     */
    private $height = null;

    /**
     * @var bool
     */
    private $fit = false;

    /**
     * @var string
     */
    private $directory = '';

    /**
     * @return string
     */
    public static function getName(): string
    {
        return "%s";
    }

    /**
     * @return int|null
     */
    public function getWidth(): ?int
    {
        return $this->width;
    }

    /**
     * @return int|null
     */
    public function getHeight(): ?int
    {
        return $this->height;
    }

    /**
     * @return bool
     */
    public function getFit(): bool
    {
        return $this->fit;
    }

    /**
     * @return string
     */
    public function getDirectory(): string
    {
        return $this->directory;
    }

}
EOD;

    /**
     * GenerateImageDefinitionCommand constructor.
     */
    public function __construct()
    {
        parent::__construct(self::getCommandName());
        $this->setDescription('Generate a new ImageDefinition');
    }

    public function configure()
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the Definition.')
            ->addArgument('repo', InputArgument::REQUIRED, 'Name of the Repository in which ImageDefinition should be generated')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $sanatizedInput = $this->sanatizeInput($input);

        if (!\is_dir(\getcwd() . '/vendor/kiwi-suite/' . $sanatizedInput['repo'])) {
            throw new \Exception(sprintf("Given Repository '%s' does not exist!", $sanatizedInput['repo']));
        }

        if (\file_exists(\getcwd() . '/vendor/kiwi-suite/' . $sanatizedInput['repo'] . '/src/ImageDefinition/' . $sanatizedInput['name'] . '.php')) {
            throw new \Exception("ImageDefinition file already exists");
        }

        if(!\is_dir(\getcwd() . '/vendor/kiwi-suite/' . $sanatizedInput['repo'] . '/src/ImageDefinition')){
            \mkdir(\getcwd() . '/vendor/kiwi-suite/' . $sanatizedInput['repo'] . '/src/ImageDefinition');
        }

        $this->generateFile($sanatizedInput);

        $output->writeln(\sprintf("<info>ImageDefinition '%s' generated</info>", $sanatizedInput['name']));
    }

    /**
     * @param array $sanatizedInput
     */
    private function generateFile(array $sanatizedInput): void
    {
        \file_put_contents(
            \getcwd() . '/vendor/kiwi-suite/' . $sanatizedInput['repo'] . '/src/ImageDefinition/' . $sanatizedInput['name'] . '.php',
            \sprintf($this->template,
                $sanatizedInput['repo'],
                $sanatizedInput['repo'],
                $sanatizedInput['repo'],
                $sanatizedInput['repo'],
                \ucfirst($sanatizedInput['repo']),
                $sanatizedInput['name'],
                $sanatizedInput['name']
            )
        );
    }

    /**
     * @return string
     */
    public static function getCommandName()
    {
        return "media:generate-imageDefinition";
    }

    /**
     * @param InputInterface $input
     * @return array
     */
    private function sanatizeInput(InputInterface $input): array {
        $sanatizeInput = [
            'name' => \trim(\ucfirst($input->getArgument('name'))),
            'repo' => \trim(\lcfirst($input->getArgument('repo')))
        ];
        return $sanatizeInput;
    }

    // TODO : Maybe auto-register generated ImageDefinitions
}