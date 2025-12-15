<?php

namespace Mostafaznv\LaraCache\Facades;

use Illuminate\Support\Facades\Facade;


/**
 * @method static update(mixed $model, string $name)
 * @method static updateAll(?string $model = null)
 * @method static delete(string $model, string $name, bool $forever = false)
 * @method static deleteAll(?string $model = null, bool $forever = false)
 * @method static retrieve(string $model, string $name, bool $withCacheData = false)
 * @method static void enable(string $model)
 * @method static void disable(string $model)
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
