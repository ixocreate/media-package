<?php
declare(strict_types=1);

namespace KiwiSuite\Media\ImageDefinition;

interface ImageDefinitionInterface
{
    public function generateImage($media);
}