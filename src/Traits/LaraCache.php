<?php

namespace Mostafaznv\LaraCache\Traits;

use Exception;
use Mostafaznv\LaraCache\Cache;
use Mostafaznv\LaraCache\CacheEntity;
use Mostafaznv\LaraCache\Observers\LaraCacheObserver;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait LaraCache
{
    public static bool $isEnabled = true;

    /**
     * Boot LaraCache
     *
     * @throws Exception
     */
    public static function bootLaraCache()
    {
        self::observe(LaraCacheObserver::class);
    }

    /**
     * Get the entities should store in cache
     *
     * @return CacheEntity[]
     */
    abstract static public function cacheEntities(): array;

    public static function cache(): Cache
    {
        return new Cache(self::class);
    }

    public static function laraCache(): Cache
    {
        return new Cache(self::class);
    }
}
