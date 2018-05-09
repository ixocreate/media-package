<?php
declare(strict_types=1);

namespace KiwiSuite\Media;

use KiwiSuite\Media\Delegator\DelegatorConfigurator;

/** @var DelegatorConfigurator $delegator */
$delegator->addDelegator(\KiwiSuite\Media\Delegator\Delegators\Image::class);