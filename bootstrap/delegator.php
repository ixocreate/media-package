<?php
declare(strict_types=1);

namespace Ixocreate\Media\Package;

use Ixocreate\Media\Package\Delegator\DelegatorConfigurator;

/** @var DelegatorConfigurator $delegator */
$delegator->addDelegator(\Ixocreate\Media\Package\Delegator\Delegators\Image::class);
