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

    private mixed  $model;
    private string $laracacheListKey;

    public function __construct(string $model)
    {
        $this->model = $model;
        $this->laracacheListKey = config('laracache.laracache-list');
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

    private function callCacheClosure(CacheEntity $entity, int $ttl, bool $delete = false): CacheData
    {
        if ($delete) {
            return CacheData::make(CacheStatus::DELETED(), $ttl, $entity->default);
        }

        $value = $entity->cacheClosure ? call_user_func($entity->cacheClosure) : null;

        return CacheData::make(
            status: CacheStatus::CREATED(),
            ttl: $ttl,
            value: $value ?: $entity->default
        );
    }

    private function updateCacheEntitiesList(CacheEntity $entity): void
    {
        $list = CacheFacade::store($entity->driver)->get($this->laracacheListKey);

        if (is_array($list)) {
            if (isset($list[$this->model]) and is_array($list[$this->model])) {
                if (!in_array($entity->name, $list[$this->model])) {
                    $list[$this->model][] = $entity->name;
                }
            }
            else {
                $list[$this->model] = [$entity->name];
            }
        }
        else {
            $list = [
                $this->model => [$entity->name]
            ];
        }

        CacheFacade::store($entity->driver)->forever($this->laracacheListKey, $list);
    }

    private function storeCache(CacheData $cache, CacheEntity $entity, int $ttl): void
    {
        is_null($cache->expiration)
            ? CacheFacade::store($entity->driver)->forever($entity->name, $cache)
            : CacheFacade::store($entity->driver)->put($entity->name, $cache, $ttl);

        $this->updateCacheEntitiesList($entity);
    }

    private function updateCacheEntity(string $name, string $event = '', CacheEntity $entity = null): CacheData
    {
        $entity = $this->findCacheEntity($name, $entity);

        if ($entity) {
            if ($this->entityIsCallable($entity, $event)) {
                $ttl = $entity->getTtl();
                $cache = $this->callCacheClosure($entity, $ttl);
                $this->storeCache($cache, $entity, $ttl);

                return $cache;
            }
            else {
                return CacheData::fromCache($entity);
            }
        }
        else {
            throw new Exception("Cache entity [$name] not found. please check if [$name] exists in " . $this->model);
        }
    }

    private function deleteCacheEntity(string $name, bool $deleteForever = false, CacheEntity $entity = null): CacheData
    {
        $entity = $this->findCacheEntity($name, $entity);

        if ($entity) {
            $ttl = !$deleteForever ? $entity->getTtl() : 0;
            $cache = $this->callCacheClosure($entity, $ttl, true);
            $this->storeCache($cache, $entity, $ttl);

            return $cache;
        }
        else {
            throw new Exception("Cache entity [$name] not found. please check if [$name] exists in " . $this->model);
        }
    }

    private function retrieve(string $name): CacheData
    {
        $entity = $this->findCacheEntity($name);

        if ($entity) {
            $cache = CacheData::fromCache($entity);

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

    public function update(string $name): CacheData
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

    public function delete(string $name, bool $forever = false): CacheData
    {
        return $this->deleteCacheEntity($name, $forever);
    }

    public function deleteAll(bool $forever = false): void
    {
        foreach ($this->model::cacheEntities() as $entity) {
            $this->deleteCacheEntity($entity->name, $forever, $entity);
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
