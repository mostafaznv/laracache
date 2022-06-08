<?php

namespace Mostafaznv\LaraCache;

use Exception;
use Illuminate\Support\Facades\Cache as CacheFacade;
use Illuminate\Database\Eloquent\Model;
use Mostafaznv\LaraCache\DTOs\CacheData;
use Mostafaznv\LaraCache\DTOs\CacheStatus;
use Mostafaznv\LaraCache\Jobs\RefreshCache;

class Cache
{
    public static string $created  = 'created';
    public static string $updated  = 'updated';
    public static string $deleted  = 'deleted';
    public static string $restored = 'restored';

    private mixed $model;

    public function __construct(string $model)
    {
        $this->model = $model;
    }


    private function driver(): ?string
    {
        return config('laracache.driver') ?? config('cache.default');
    }

    private function findCacheEntity(string $name, ?CacheEntity $entity = null): ?CacheEntity
    {
        if (is_null($entity)) {
            foreach ($this->model::cacheEntities() as $cacheEntity) {
                if ($cacheEntity->name === $name) {
                    return $cacheEntity;
                }
            }
        }

        return $entity;
    }

    private function entityIsCallable(CacheEntity $entity, string $event = ''): bool
    {
        return $event == ''
            or ($event == self::$created and $entity->refreshAfterCreate)
            or ($event == self::$updated and $entity->refreshAfterUpdate)
            or ($event == self::$deleted and $entity->refreshAfterDelete)
            or ($event == self::$restored and $entity->refreshAfterRestore);
    }

    private function isQueueable(): bool
    {
        return config('laracache.queue') ?? false;
    }

    private function updateCacheEntity(string $name, string $event = '', CacheEntity $entity = null): CacheData
    {
        $entity = $this->findCacheEntity($name, $entity);

        if ($entity) {
            $driver = $this->driver();

            if ($this->entityIsCallable($entity, $event)) {
                $value = $entity->cacheClosure ? call_user_func($entity->cacheClosure) : null;
                $value = $value ?: $entity->default;

                if ($entity->forever) {
                    $value = CacheData::make(CacheStatus::CREATED(), 0, $value);

                    CacheFacade::store($driver)->forever($entity->name, $value);
                }
                else {
                    if ($entity->validForRestOfDay) {
                        $ttl = day_ending_seconds();
                    }
                    else if ($entity->validForRestOfWeek) {
                        $ttl = week_ending_seconds();
                    }
                    else {
                        $ttl = $entity->ttl;
                    }

                    $value = CacheData::make(CacheStatus::CREATED(), $ttl, $value);

                    CacheFacade::store($driver)->put($entity->name, $value, $ttl);
                }

                return $value;
            }
            else {
                return CacheData::fromCache($entity, $driver);
            }
        }
        else {
            throw new Exception("Cache entity [$name] not found. please check if [$name] exists in " . $this->model);
        }
    }

    private function retrieve(string $name): CacheData
    {
        $driver = $this->driver();

        foreach ($this->model::cacheEntities() as $entity) {
            if ($entity->name == $name) {
                $cache = CacheData::fromCache($entity, $driver);

                if ($cache->status->equals(CacheStatus::NOT_CREATED())) {
                    return $this->updateCacheEntity($name, '', $entity);
                }

                return $cache;
            }
        }

        throw new Exception("Cache entity [$name] not found. please check if [$name] exists in " . $this->model);
    }


    public function refresh(Model $model, string $event): void
    {
        if ($this->model::$isEnabled) {
            if ($this->isQueueable()) {
                RefreshCache::dispatch($model, $event);
            }
            else {
                foreach ($this->model::cacheEntities() as $entity) {
                    $this->updateCacheEntity($entity->name, $event, $entity);
                }
            }
        }
    }

    public function get(string $name, bool $withCacheData = false): mixed
    {
        $cache = $this->retrieve($name);

        if ($withCacheData) {
            return $cache;
        }

        return $cache->value;
    }

    public function update(string $name): mixed
    {
        return $this->updateCacheEntity($name);
    }

    public function updateAll(): void
    {
        foreach ($this->model::cacheEntities() as $entity) {
            $this->updateCacheEntity(
                name: $entity->name,
                entity: $entity
            );
        }
    }

    public function disable(): void
    {
        $this->model::$isEnabled = false;
    }

    public function enable(): void
    {
        $this->model::$isEnabled = true;
    }
}
