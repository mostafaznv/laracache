<?php

namespace Mostafaznv\LaraCache\Traits;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Mostafaznv\LaraCache\CacheEntity;
use Mostafaznv\LaraCache\Jobs\RefreshCache;
use Mostafaznv\LaraCache\Utils\Helpers;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait LaraCache
{
    private static string $created = 'created';
    private static string $updated = 'updated';
    private static string $deleted = 'deleted';

    private static bool $isEnabled = true;

    /**
     * Boot LaraCache
     *
     * @throws Exception
     */
    public static function bootLaraCache()
    {
        self::created(fn($model) => $model->refreshCache($model, self::$created));

        self::updated(fn($model) => $model->refreshCache($model, self::$updated));

        self::deleted(fn($model) => $model->refreshCache($model, self::$deleted));
    }

    /**
     * Get the entities should store in cache
     *
     * @return CacheEntity[]
     */
    abstract public function cacheEntities(): array;

    /**
     * Get cache driver
     *
     * @return string|null
     */
    private function cacheDriver(): ?string
    {
        return config('laracache.driver') ?? config('cache.default');
    }

    /**
     * Check if process is queueable
     *
     * @return bool
     */
    private function isQueueable(): bool
    {
        return config('laracache.queue') ?? false;
    }

    /**
     * Refresh all cache entities after model events
     *
     * @param Model $model
     * @param string $event
     * @throws InvalidArgumentException
     */
    private function refreshCache(Model $model, string $event): void
    {
        if (self::$isEnabled) {
            if ($this->isQueueable()) {
                RefreshCache::dispatch($model, $event);
            }
            else {
                foreach ($this->cacheEntities() as $entity) {
                    $this->updateCacheEntity($entity->name, $event, $entity);
                }
            }
        }
    }

    /**
     * Update cache
     *
     * @param string $name
     * @param string $event
     * @param CacheEntity|null $entity
     * @return mixed
     * @throws InvalidArgumentException|Exception
     */
    private function updateCacheEntity(string $name, string $event = '', CacheEntity $entity = null): mixed
    {
        if (is_null($entity)) {
            foreach ($this->cacheEntities() as $cacheEntity) {
                if ($cacheEntity->name == $name) {
                    $entity = $cacheEntity;

                    break;
                }
            }
        }

        if ($entity) {
            $driver = $this->cacheDriver();

            if ($event == '' or ($event == self::$created and $entity->refreshAfterCreate) or ($event == self::$updated and $entity->refreshAfterUpdate) or ($event == self::$deleted and $entity->refreshAfterDelete)) {
                $value = call_user_func($entity->cacheClosure);

                if ($entity->forever) {
                    Cache::store($driver)->forever($entity->name, $value);
                }
                else {
                    if ($entity->validForRestOfDay) {
                        $ttl = Helpers::timeToEndOfDay();
                    }
                    else if ($entity->validForRestOfWeek) {
                        $ttl = Helpers::timeToEndOfWeek();
                    }
                    else {
                        $ttl = $entity->ttl;
                    }

                    Cache::store($driver)->put($entity->name, $value, $ttl);
                }

                return $value;
            }
            else {
                return Cache::store($driver)->get($entity->name, $entity->default);
            }
        }
        else {
            throw new Exception("Cache entity [$name] not found. please check if [$name] exists in " . self::class);
        }
    }

    /**
     * Retrieve cache
     *
     * @param string $name
     * @return mixed
     * @throws InvalidArgumentException|Exception
     */
    private function cache(string $name): mixed
    {
        $driver = $this->cacheDriver();

        foreach ($this->cacheEntities() as $key => $entity) {
            if ($entity->name == $name) {
                $value = Cache::store($driver)->get($entity->name, $entity->default);

                if ($value) {
                    return $value;
                }
                else {
                    return $this->updateCacheEntity($name, '', $entity);
                }
            }
        }

        throw new Exception("Cache entity [$name] not found. please check if [$name] exists in " . self::class);
    }

    //----------------Public Trait Methods ---------------------------------------------------------------------------//

    /**
     * Static method to update cache
     *
     * @param string $name
     * @param string $event
     * @param CacheEntity|null $entity
     * @return mixed
     * @throws InvalidArgumentException|Exception
     */
    public static function updateCache(string $name, string $event = '', CacheEntity $entity = null): mixed
    {
        return (new self())->updateCacheEntity($name, $event, $entity);
    }

    /**
     * Static method to update all cache entities
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public static function updateAllCacheEntities(): void
    {
        $class = new self();

        foreach ($class->cacheEntities() as $entity) {
            $class->updateCacheEntity(
                name: $entity->name,
                entity: $entity
            );
        }
    }

    /**
     * Static method to retrieve cache
     *
     * @param string $name
     * @return mixed
     * @throws InvalidArgumentException|Exception
     */
    public static function retrieveCache(string $name): mixed
    {
        return (new self())->cache($name);
    }

    /**
     * Disable refresh cache on all events
     */
    public static function disableCache(): void
    {
        self::$isEnabled = false;
    }

    /**
     * Enable refresh cache on all events
     */
    public static function enableCache(): void
    {
        self::$isEnabled = true;
    }
}
