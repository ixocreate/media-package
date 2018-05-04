<?php
/**
 * Created by PhpStorm.
 * User: afriedrich
 * Date: 04.05.18
 * Time: 13:28
 */

namespace KiwiSuite\Media\Factory;


use KiwiSuite\Contract\ServiceManager\FactoryInterface;
use KiwiSuite\Contract\ServiceManager\ServiceManagerInterface;

class MediaConfigFactory implements FactoryInterface
{
    public function __invoke(ServiceManagerInterface $container, $requestedName, array $options = null)
    {
        // TODO: Implement __invoke() method.
    }
}