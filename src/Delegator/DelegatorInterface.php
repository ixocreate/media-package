<?php
declare (strict_types=1);

namespace KiwiSuite\Media\Delegator;

use KiwiSuite\Media\Entity\Media;

interface DelegatorInterface
{
    public static function getName() : string;

    public function responsible(Media $media);
}