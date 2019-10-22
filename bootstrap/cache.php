<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Cache;

use Ixocreate\Cache\CacheConfigurator;
use Ixocreate\Cache\Option\Chain;
use Ixocreate\Cache\Option\Filesystem;
use Ixocreate\Cache\Option\InMemory;
use Ixocreate\Media\Cacheable\UrlVariantCacheable;

/** @var CacheConfigurator $cache */
$cache->addCacheable(UrlVariantCacheable::class);

$cache->addCache('media', new InMemory());
