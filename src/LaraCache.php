<?php

namespace Mostafaznv\LaraCache;

use Illuminate\Support\Facades\Cache;

class LaraCache
{
    /**
     * Update Cache Entity
     *
     * @param mixed $model
     * @param string $name
     * @param string $event
     * @param CacheEntity|null $entity
     *
     * @return mixed
     */
    public function update(mixed $model, string $name, string $event = '', ?CacheEntity $entity = null): mixed
    {
        return $model::cache()->update($name, $event, $entity);
    }

    /**
     * Update All Cache Entities
     *
     * @param mixed $model
     */
    public function updateAll(mixed $model = null): void
    {
        if ($model) {
            $model::cache()->updateAll();
        }
        else {
            $list = self::list();

            /** @var mixed $model */
            foreach ($list as $model => $entities) {
                $model::cache()->updateAll();
            }
        }
    }

    /**
     * Delete Cache Entity
     *
     * @param mixed $model
     * @param string $name
     * @param bool $forever
     * @return mixed
     */
    public function delete(mixed $model, string $name, bool $forever = false): mixed
    {
        return $model::cache()->delete($name, $forever);
    }

    /**
     * Delete All Cache Entities
     *
     * @param mixed $model
     * @param bool $forever
     */
    public function deleteAll(mixed $model = null, bool $forever = false): void
    {
        if ($model) {
            $model::cache()->deleteAll($forever);
        }
        else {
            $list = self::list();

            /** @var mixed $model */
            foreach ($list as $model => $entities) {
                $model::cache()->deleteAll($forever);
            }
        }
    }

    /**
     * Retrieve Cache
     *
     * @param mixed $model
     * @param string $name
     * @param bool $withCacheData
     * @return mixed
     */
    public function retrieve(mixed $model, string $name, bool $withCacheData = false): mixed
    {
        return $model::cache()->get($name, $withCacheData);
    }

    /**
     * Disable refresh cache on all events
     *
     * @param $model
     */
    public function disable($model): void
    {
        $model::cache()->disable();
    }

    /**
     * Enable refresh cache on all events
     *
     * @param $model
     */
    public function enable($model): void
    {
        $model::cache()->enable();
    }

    /**
     * Retrieve List of All Cache Entities
     *
     * @return array
     */
    public function list(): array
    {
        $laracacheListKey = config('laracache.laracache-list');
        $driver = config('laracache.driver') ?? config('cache.default');

        return Cache::store($driver)->get($laracacheListKey, []);
    }
}
