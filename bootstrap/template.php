<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media;

use Ixocreate\Media\Template\MediaExtension;
use Ixocreate\Template\TemplateConfigurator;

/** @var TemplateConfigurator $template */
$template->addExtension(MediaExtension::class);
