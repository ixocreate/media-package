<?php
declare(strict_types=1);

namespace Ixocreate\Package\Media;

use Ixocreate\Package\Media\Delegator\DelegatorConfigurator;

/** @var DelegatorConfigurator $delegator */
$delegator->addDelegator(\Ixocreate\Package\Media\Delegator\Delegators\Image::class);
