<?php

namespace Mostafaznv\LaraCache\Facades;

use Illuminate\Support\Facades\Facade;
use Mostafaznv\LaraCache\CacheEntity;


/**
 * @method static update(mixed $model, string $name, string $event = '', CacheEntity $entity = null)
 * @method static updateAll(mixed $model)
 * @method static delete(mixed $model, string $name, bool $forever = false)
 * @method static deleteAll(mixed $model = null, bool $forever = false)
 * @method static retrieve(mixed $model, string $name, bool $withCacheData = false)
 * @method static void enable(mixed $model)
 * @method static void disable(mixed $model)
 * @method static array list()
 *
 * @see \Mostafaznv\LaraCache\LaraCache
 */
class LaraCache extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laracache';
    }
}
