<?php

namespace Mostafaznv\LaraCache;

use Illuminate\Support\Facades\Cache;


class LaraCache
{
    /**
     * @param class-string $model
     */
    public function update(string $model, string $name): mixed
    {
        return $model::cache()->update($name);
    }

    /**
     * @param class-string|null $model
     */
    public function updateAll(?string $model = null): void
    {
        if ($model) {
            $model::cache()->updateAll();
        }
        else {
            $list = self::list();

            /** @var class-string $model */
            foreach ($list as $model => $entities) {
                $model::cache()->updateAll();
            }
        }
    }

    /**
     * @param class-string $model
     */
    public function delete(string $model, string $name, bool $forever = false): mixed
    {
        return $model::cache()->delete($name, $forever);
    }

    /**
     * @param class-string|null $model
     */
    public function deleteAll(?string $model = null, bool $forever = false): void
    {
        if ($model) {
            $model::cache()->deleteAll($forever);
        }
        else {
            $list = self::list();

            /** @var class-string $model */
            foreach ($list as $model => $entities) {
                $model::cache()->deleteAll($forever);
            }
        }
    }

    /**
     * @param class-string $model
     */
    public function retrieve(string $model, string $name, bool $withCacheData = false): mixed
    {
        return $model::cache()->get($name, $withCacheData);
    }

    /**
     * @param class-string $model
     */
    public function disable(string $model): void
    {
        $model::cache()->disable();
    }

    /**
     * @param class-string $model
     */
    public function enable(string $model): void
    {
        $model::cache()->enable();
    }

    public function list(): array
    {
        $laracacheListKey = config('laracache.laracache-list');
        $driver = config('laracache.driver') ?? config('cache.default');

        return Cache::store($driver)->get($laracacheListKey, []);
    }
}
