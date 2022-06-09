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
    private string $laracacheListKey;

    public function __construct(string $model)
    {
        $this->model = $model;
        $this->laracacheListKey = config('laracache.laracache-list');
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

    private function callCacheClosure(CacheEntity $entity, int $ttl): CacheData
    {
        $value = $entity->cacheClosure ? call_user_func($entity->cacheClosure) : null;

        return CacheData::make(
            status: CacheStatus::CREATED(),
            ttl: $ttl,
            value: $value ?: $entity->default
        );
    }

    private function storeCache(CacheData $cache, CacheEntity $entity, int $ttl, string $driver): void
    {
        is_null($cache->expiration)
            ? CacheFacade::store($driver)->forever($entity->name, $cache)
            : CacheFacade::store($driver)->put($entity->name, $cache, $ttl);

        $list = CacheFacade::store($driver)->get($this->laracacheListKey);

        if (is_array($list)) {
            $list[] = $entity->name;
        }
        else {
            $list = [$entity->name];
        }

        CacheFacade::store($driver)->forever($this->laracacheListKey, $list);
    }

    private function updateCacheEntity(string $name, string $event = '', CacheEntity $entity = null): CacheData
    {
        $entity = $this->findCacheEntity($name, $entity);

        if ($entity) {
            $driver = $this->driver();

            if ($this->entityIsCallable($entity, $event)) {
                $ttl = $entity->getTtl();
                $cache = $this->callCacheClosure($entity, $ttl);
                $this->storeCache($cache, $entity, $ttl, $driver);

                return $cache;
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
        $entity = $this->findCacheEntity($name);

        if ($entity) {
            $cache = CacheData::fromCache($entity, $driver);

            if ($cache->status->equals(CacheStatus::NOT_CREATED())) {
                return $this->updateCacheEntity($name, '', $entity);
            }

            return $cache;
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
