<?php

namespace Mostafaznv\LaraCache\Facades;

use Illuminate\Support\Facades\Facade;
use Mostafaznv\LaraCache\CacheEntity;


/**
 * @method static update($model, string $name, string $event = '', CacheEntity $entity = null)
 * @method static updateAll($model)
 * @method static retrieve($model, string $name)
 * @method static void enable($model)
 * @method static void disable($model)
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
