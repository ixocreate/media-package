<?php
/**
 * Created by PhpStorm.
 * User: afriedrich
 * Date: 20.08.18
 * Time: 07:54
 */

namespace KiwiSuite\Media\Processor;


use KiwiSuite\Contract\ServiceManager\NamedServiceInterface;

interface ProcessorInterface extends NamedServiceInterface
{
    public function process();
}