<?php
declare(strict_types=1);

namespace Ixocreate\Media;

use Ixocreate\Media\Delegator\DelegatorConfigurator;

/** @var DelegatorConfigurator $delegator */
$delegator->addDelegator(\Ixocreate\Media\Delegator\Delegators\Image::class);