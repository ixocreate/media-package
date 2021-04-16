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

/** @var CacheConfigurator $cache */
$cache->addCacheable(MediaCacheable::class);

$cache->addCache('media', new InMemory());
