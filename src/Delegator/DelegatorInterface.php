<?php
declare (strict_types=1);

namespace KiwiSuite\Media\Delegator;

use KiwiSuite\Media\Entity\Media;

interface DelegatorInterface
{
    public function responsible(Media $media);

    public function process(Media $media);
}