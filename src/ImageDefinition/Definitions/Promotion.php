<?php
declare(strict_types=1);

namespace KiwiSuite\Media\ImageDefinition\Definitions;

use KiwiSuite\Media\ImageDefinition\ImageDefinitionInterface;

final class Promotion implements ImageDefinitionInterface
{
    /**
     * @var int
     */
    private $width = 680;

    /**
     * @var null
     */
    private $height = null;

    /**
     * @var bool
     */
    private $fit = false;

    /**
     * @var string
     */
    private $directory = 'promotion';

    /**
     * @return string
     */
    public static function getName(): string
    {
        return "Promotion";
    }

    /**
     * @return mixed
     */
    public function getWidth(): ?int
    {
        return $this->width;
    }

    /**
     * @return mixed
     */
    public function getHeight(): ?int
    {
        return $this->height;
    }

    /**
     * @return bool
     */
    public function getFit()
    {
        return $this->fit;
    }

    /**
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

}