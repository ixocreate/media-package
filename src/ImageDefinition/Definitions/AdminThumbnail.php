<?php
declare(strict_types=1);

namespace KiwiSuite\Media\ImageDefinition\Definitions;

use KiwiSuite\Media\ImageDefinition\ImageDefinitionInterface;

final class AdminThumb implements ImageDefinitionInterface
{
    /**
     * @var int
     */
    private $width = 500;

    /**
     * @var int
     */
    private $height = 500;

    /**
     * @var bool
     */
    private $fit = true;

    /**
     * @var string
     */
    private $directory = 'admin-thumb';

    /**
     * @return string
     */
    public static function getName(): string
    {
        return "AdminThumb";
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return mixed
     */
    public function getHeight()
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
    public function getDirectory(): string
    {
        return $this->directory;
    }

}