<?php
/**
 * Created by PhpStorm.
 * User: afriedrich
 * Date: 10.08.18
 * Time: 10:39
 */

namespace KiwiSuite\Media\BootstrapItem;


use KiwiSuite\Contract\Application\BootstrapItemInterface;
use KiwiSuite\Contract\Application\ConfiguratorInterface;
use KiwiSuite\Media\Config\MediaConfigurator;

class MediaBootstrapItem implements BootstrapItemInterface
{

    public function getConfigurator(): ConfiguratorInterface
    {
        return new MediaConfigurator();
    }
    public function getVariableName(): string
    {
        return 'media';
    }
    public function getFileName(): string
    {
        return 'media.php';
    }
}