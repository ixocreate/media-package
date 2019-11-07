<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Cache;

use Ixocreate\Cache\CacheConfigurator;
use Ixocreate\Cache\Option\InMemory;
use Ixocreate\Media\Cacheable\MediaCacheable;
use Ixocreate\Media\Cacheable\MediaDefinitionInfoCacheable;
use Ixocreate\Media\Cacheable\UrlVariantCacheable;

/** @var CacheConfigurator $cache */
$cache->addCacheable(UrlVariantCacheable::class);
$cache->addCacheable(MediaDefinitionInfoCacheable::class);
$cache->addCacheable(MediaCacheable::class);

$cache->addCache('media', new InMemory());
