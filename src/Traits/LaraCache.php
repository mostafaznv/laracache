<?php

namespace Mostafaznv\LaraCache\Traits;

use Exception;
use Mostafaznv\LaraCache\Cache;
use Mostafaznv\LaraCache\CacheEntity;

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
        self::created(fn($model) => $model->cache()->refresh($model, Cache::$created));

        self::updated(fn($model) => $model->cache()->refresh($model, Cache::$updated));

        self::deleted(fn($model) => $model->cache()->refresh($model, Cache::$deleted));
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
