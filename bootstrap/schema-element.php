<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Schema;

use Ixocreate\Media\Schema\Element\AudioElement;
use Ixocreate\Media\Schema\Element\DocumentElement;
use Ixocreate\Media\Schema\Element\ImageElement;
use Ixocreate\Media\Schema\Element\MediaElement;
use Ixocreate\Media\Schema\Element\VideoElement;
use Ixocreate\Schema\Element\ElementConfigurator;

/** @var ElementConfigurator $element */
$element->addElement(ImageElement::class);
$element->addElement(DocumentElement::class);
$element->addElement(MediaElement::class);
$element->addElement(AudioElement::class);
$element->addElement(VideoElement::class);
