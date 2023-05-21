# LaraCache

[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/mostafaznv/laracache/run-tests.yml?branch=master&label=Build&style=flat-square&logo=github)](https://github.com/mostafaznv/laracache/actions)
[![Codecov branch](https://img.shields.io/codecov/c/github/mostafaznv/laracache/master.svg?style=flat-square&logo=codecov)](https://app.codecov.io/gh/mostafaznv/laracache)
[![Quality Score](https://img.shields.io/scrutinizer/g/mostafaznv/laracache.svg?style=flat-square)](https://scrutinizer-ci.com/g/mostafaznv/laracache)
[![GitHub license](https://img.shields.io/github/license/mostafaznv/laracache?style=flat-square)](https://github.com/mostafaznv/laracache/blob/master/LICENSE)
[![Packagist Downloads](https://img.shields.io/packagist/dt/mostafaznv/laracache?style=flat-square&logo=packagist)](https://packagist.org/packages/mostafaznv/laracache)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/mostafaznv/laracache.svg?style=flat-square&logo=composer)](https://packagist.org/packages/mostafaznv/laracache)


Using this package, you can cache your heavy and most used queries.

All you have to do is to define the `CacheEntity` objects in the model and specify a valid name and ttl for them.

LaraCache will handle the rest of process automatically. It will create and update cache entities based on ttl that you've defined for each entity.

Manually updating the cache entities of models after dispatching model events (creating, updating and deleting) isn't required, LaraCache manages them in the background and ensures the most up-to-date version of each cache entity.



----
I develop in an open-source journey 🚀, I wish I lived in an environment where financial situation was fine and I could only focus on the path, but as you may know, life isn't perfect. <br>So if you end up using my packages, please consider making a donation, any amount would go along way and is much appreciated. 🍺

[![Donate](https://mostafaznv.github.io/donate/donate.svg)](https://mostafaznv.github.io/donate)

----


## Requirements:

- PHP 8.0.2 or higher
- Laravel 8.40.0 or higher


## Installation

1. ##### Install the package via composer:
    ```shell
    composer require mostafaznv/laracache
    ```

2. ##### Publish config file:
    ```shell
    php artisan vendor:publish --provider="Mostafaznv\LaraCache\LaraCacheServiceProvider"
    ```

3. ##### Done


## Usage

1. ##### Add LaraCache trait to the model
    ```php
    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    use Mostafaznv\LaraCache\Traits\LaraCache;
    
    class Article extends Model
    {
        use LaraCache;
        
        /**
         * Define Cache Entities Entities
         *
         * @return CacheEntity[]
         */
        public static function cacheEntities(): array
        {
            return [
                CacheEntity::make('list.forever')
                    ->cache(function() {
                        return Article::query()->latest()->get();
                    }),
   
                CacheEntity::make('latest')
                    ->validForRestOfDay()
                    ->cache(function() {
                        return Article::query()->latest()->first();
                    })
            ];
        }
    }
    ```

2. ##### Retrieve Cache
    ```php
    use App\Models\Article;
    use Mostafaznv\LaraCache\Facades\LaraCache;
   
   
    $cache = Article::cache()->get('latest');
    // or
    $cache = LaraCache::retrieve(Article::class, 'latest');
    ```


## Table of Contents:
- [Installation](#installation)
    - [Install the package via composer](#install-the-package-via-composer)
    - [Publish config file](#publish-config-file)
- [Usage](#usage)
- [CacheEntity Methods](#cacheentity-methods)
- [Disable/Enable Cache](#disableenable-cache)
    - [Enable](#enable)
    - [Disable](#disable)
- [Update Cache Manually](#update-cache-manually)
    - [Update an Entity](#update-an-entity)
    - [Update all Entities](#update-all-entities)
    - [Update all LaraCache Entities](#update-all-laracache-entities)
- [Delete Cache Manually](#delete-cache-manually)
    - [Delete an Entity](#delete-an-entity)
    - [Delete an Entity Forever](#delete-an-entity-forever)
    - [Delete all Model Entities](#delete-all-model-entities)
    - [Delete all Model Entities Forever](#delete-all-model-entities-forever)
    - [Delete all LaraCache Entities](#delete-all-laracache-entities)
- [Artisan Commands](#artisan-commands)
    - [Update Cache](#update-cache)
- [Config Properties](#config-properties)
- [Complete Example](#complete-example)



## CacheEntity Methods

| method               | Arguments                               | description                                                                   |
|----------------------|-----------------------------------------|-------------------------------------------------------------------------------|
| setDriver            | driver (type: `string`)                 | Specifies custom driver for cache entity                                      |
| isQueueable          | status (type: `bool`, default: 'true')  | Specifies if cache operation should perform in the background or not          |
| refreshAfterCreate   | status (type: `bool`, default: `true`)  | Specifies if the cache should refresh after create a record                   |
| refreshAfterUpdate   | status (type: `bool`, default: `true`)  | Specifies if the cache should refresh after update a record                   |
| refreshAfterDelete   | status (type: `bool`, default: `true`)  | Specifies if the cache should refresh after delete a record                   |
| refreshAfterRestore  | status (type: `bool`, default: `true`)  | Specifies if the cache should refresh after restore a record                  |
| forever              |                                         | Specifies that the cache should be valid forever                              |
| validForRestOfDay    |                                         | Specify that cache entity should be valid till end of day                     |
| validForRestOfWeek   |                                         | Specify that cache entity should be valid till end of week                    |
| ttl                  | seconds (type: `int`)                   | Specifies cache time to live in second                                        |
| setDefault           | defaultValue (type: `mixed`)            | Specifies default value for the case that cache entity doesn't have any value |
| cache                | Closure                                 | **Main** part of each cache entity. defines cache content                     |


## Disable/Enable Cache

If you want to disable/enable cache, you can do it in the following two ways:

### Disable

```php
use App\Models\Article;
use Mostafaznv\LaraCache\Facades\LaraCache;


Article::cache()->disable();
// or 
LaraCache::disable(Article::class);
```


### Enable

```php
use App\Models\Article;
use Mostafaznv\LaraCache\Facades\LaraCache;


Article::cache()->enable();
// or 
LaraCache::enable(Article::class);
```

## Update Cache Manually

Sometimes you want to update cache entities manually.

### Update an Entity

```php
use App\Models\Article;
use Mostafaznv\LaraCache\Facades\LaraCache;


Article::cache()->update('latest');
// or 
LaraCache::update(Article::class, 'latest');
```

### Update all Entities

```php
use App\Models\Article;
use Mostafaznv\LaraCache\Facades\LaraCache;


Article::cache()->updateAll();
// or 
LaraCache::updateAll(Article::class);
```

### Update all LaraCache Entities

This will update all cache entities that stored using LaraCache (across all models)

```php
use App\Models\Article;
use Mostafaznv\LaraCache\Facades\LaraCache;

 
LaraCache::updateAll();
```

## Delete Cache Manually

Sometimes you want to delete cache entities manually. using these methods, you can do it.

### Delete an Entity

Using this feature, you can delete cache entities temporary. after spending ttl, cache entity will be generated again.

```php
use App\Models\Article;
use Mostafaznv\LaraCache\Facades\LaraCache;


Article::cache()->delete('latest');
// or 
LaraCache::delete(Article::class, 'latest');
```

### Delete an Entity Forever

Using this feature, you can delete cache entities permanently. Cache item will be deleted forever and whenever you try to retrieve it, you will get null (or default value).



```php
use App\Models\Article;
use Mostafaznv\LaraCache\Facades\LaraCache;


Article::cache()->delete('latest', true);
// or 
LaraCache::delete(Article::class, 'latest', true);
```

> Note: Cache Entity will update after creating or updating records in your model


### Delete all Model Entities

```php
use App\Models\Article;
use Mostafaznv\LaraCache\Facades\LaraCache;


Article::cache()->deleteAll();
// or 
LaraCache::deleteAll(Article::class);
```



### Delete all Model Entities Forever

```php
use App\Models\Article;
use Mostafaznv\LaraCache\Facades\LaraCache;


Article::cache()->deleteAll(true);
// or 
LaraCache::deleteAll(Article::class, true);
```



### Delete all LaraCache Entities

This will delete all cache entities that stored using LaraCache (across all models)

```php
use App\Models\Article;
use Mostafaznv\LaraCache\Facades\LaraCache;

LaraCache::deleteAll();
// forever
LaraCache::deleteAll(forever: true);
```




## Artisan Commands
This feature allows you to update or delete multiple cache entities of one or more models from the console command. This means you can programmatically control the cache data outside the caching cycle.

### Update Cache
```shell
php artisan laracache:update -m Article
php artisan laracache:update -m Article -e latest -e featured
php artisan laracache:update -m Article -m Product
php artisan laracache:update -m Domain\Article\Models\Article
```

> Note: If you don't specify any entity, all entities will be updated.

> Note: If you specify multiple models, you can't specify any entity and all entities of all models will be updated.

## Config Properties

| method                   | Type                                 | description                                                                                                                                                     |
|--------------------------|--------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------|
| driver                   | string (default: `null`)             | The default mechanism for handling cache storage.<br>If you keep this option `null`, LaraCache will use the default cache storage from `config/cache.php`       |
| laracache-list           | string (default: `laracache.list`)   | LaraCache uses a separate list to store name of all entities. using these keys, we can perform some actions to all entities (such as update or delete them)     |
| first-day-of-week        | integer (default: `0`)               | In some regions, saturday is first day of the week and in another regions it may be different. you can change the first day of a week by changing this property |
| last-day-of-week         | integer (default: `6`)               | In some regions, friday is last day of the week and in another regions it may be different. you can change the last day of a week by changing this property     |
| queue                    | bool (default: `false`)              | Sometimes caching process is very heavy, so you have to queue the process and do it in background.                                                              |


## Complete Example
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Mostafaznv\LaraCache\Traits\LaraCache;

class Article extends Model
{
    use LaraCache;
    
    /**
     * Define Cache Entities Entities
     *
     * @return CacheEntity[]
     */
    public static function cacheEntities(): array
    {
        return [
            CacheEntity::make('list.forever')
                ->forever()
                ->setDriver('redis')
                ->cache(function() {
                    return Article::query()->latest()->get();
                }),

            CacheEntity::make('list.day')
                ->isQueueable()
                ->validForRestOfDay()
                ->cache(function() {
                    return Article::query()->latest()->get();
                }),

            CacheEntity::make('list.week')
                ->validForRestOfWeek()
                ->cache(function() {
                    return Article::query()->latest()->get();
                }),

            CacheEntity::make('list.ttl')
                ->ttl(120)
                ->cache(function() {
                    return Article::query()->latest()->get();
                }),

            CacheEntity::make('latest')
                ->forever()
                ->cache(function() {
                    return Article::query()->latest()->first();
                }),

            CacheEntity::make('latest.no-create')
                ->refreshAfterCreate(false)
                ->cache(function() {
                    return Article::query()->latest()->first();
                }),

            CacheEntity::make('latest.no-update')
                ->refreshAfterUpdate(false)
                ->cache(function() {
                    return Article::query()->latest()->first();
                }),

            CacheEntity::make('latest.no-delete')
                ->refreshAfterDelete(false)
                ->cache(function() {
                    return Article::query()->latest()->first();
                }),

            CacheEntity::make('latest.no-restore')
                ->refreshAfterRestore(false)
                ->cache(function() {
                    return Article::query()->latest()->first();
                }),

            CacheEntity::make('empty.array')
                ->setDefault('empty value')
                ->cache(fn() => []),
        ];
    }
}
```

----
I develop in an open-source journey 🚀, I wish I lived in an environment where financial situation was fine and I could only focus on the path, but as you may know, life isn't perfect. <br>So if you end up using my packages, please consider making a donation, any amount would go along way and is much appreciated. 🍺

[![Donate](https://mostafaznv.github.io/donate/donate.svg)](https://mostafaznv.github.io/donate)

----



## License

This software is released under [The MIT License (MIT)](LICENSE).
