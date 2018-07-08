<?php
declare(strict_types=1);

namespace KiwiSuite\Media;

/** @var ResourceConfigurator $resource */
use KiwiSuite\Media\Resource\MediaResource;
use KiwiSuite\Resource\SubManager\ResourceConfigurator;

$resource->addResource(MediaResource::class);
