# LaraCache

[![GitHub Workflow Status](https://img.shields.io/github/workflow/status/mostafaznv/laracahe/Run%20Tests?label=Build&style=flat-square)](https://github.com/mostafaznv/laracache/actions)
[![GitHub license](https://img.shields.io/github/license/mostafaznv/laracache?style=flat-square)](https://github.com/mostafaznv/laracache/blob/master/LICENSE)
[![Quality Score](https://img.shields.io/scrutinizer/g/mostafaznv/laracache.svg?style=flat-square)](https://scrutinizer-ci.com/g/mostafaznv/laracache)
[![Packagist Downloads](https://img.shields.io/packagist/dt/mostafaznv/laracache?style=flat-square)](https://packagist.org/packages/mostafaznv/laracache)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/mostafaznv/laracache.svg?style=flat-square)](https://packagist.org/packages/mostafaznv/laracache)


Using this package, you can cache your heavy and most used queries.

All you have to do is to define the `CacheEntity` objects in the model and specify a valid name and ttl for them.

LaraCache will handle the rest of process automatically. It will create and update cache entities based on ttl that you've defined for each entity.

Manually updating the cache entities of models after dispatching model events (creating, updating and deleting) isn't required, LaraCache manages them in the background and ensures the most up-to-date version of each cache entity.

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
        public function cacheEntities(): array
        {
            return [
                CacheEntity::make('list.forever')
                    ->cache(function() {
                        return TestModel::query()->latest()->get();
                    }),
   
                CacheEntity::make('latest')
                    ->validForRestOfDay()
                    ->cache(function() {
                        return TestModel::query()->latest()->first();
                    })
            ];
        }
    }
    ```

2. ##### Retrieve Cache
    ```php
    use App\Models\Article;
    use Mostafaznv\LaraCache\Facades\LaraCache;
   
   
    $cache = Article::retrieveCache('latest');
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
- [Config Properties](#config-properties)



## CacheEntity Methods

| method             | Arguments                              | description                                                                   |
|--------------------|----------------------------------------|-------------------------------------------------------------------------------|
| refreshAfterCreate | status (type: `bool`, default: `true`) | Specifies if the cache should refresh after create a record                   |
| refreshAfterUpdate | status (type: `bool`, default: `true`) | Specifies if the cache should refresh after update a record                   |
| refreshAfterDelete | status (type: `bool`, default: `true`) | Specifies if the cache should refresh after delete a record                   |
| forever            |                                        | Specifies that the cache should be valid forever                              |
| validForRestOfDay  |                                        | Specify that cache entity should be valid till end of day                     |
| validForRestOfWeek |                                        | Specify that cache entity should be valid till end of week                    |
| ttl                | seconds (type: `int`)                  | Specifies cache time to live in second                                        |
| setDefault         | defaultValue (type: `mixed`)           | Specifies default value for the case that cache entity doesn't have any value |
| cache              | Closure                                | **Main** part of each cache entity. defines cache content                     |


## Disable/Enable Cache

If you want to disable/enable cache, you can do it in the following two ways:

### Disable

```php
use App\Models\Article;
use Mostafaznv\LaraCache\Facades\LaraCache;


Article::disableCache();
// or 
LaraCache::disable(Article::class);
```


### Enable

```php
use App\Models\Article;
use Mostafaznv\LaraCache\Facades\LaraCache;


Article::enableCache();
// or 
LaraCache::enable(Article::class);
```

## Update Cache Manually

Sometimes you want to update cache entities manually.

### Update an Entity

```php
use App\Models\Article;
use Mostafaznv\LaraCache\Facades\LaraCache;


Article::updateCache('latest');
// or 
LaraCache::update(Article::class, 'latest');
```

### Update all Entities

```php
use App\Models\Article;
use Mostafaznv\LaraCache\Facades\LaraCache;


Article::updateAllCacheEntities('latest');
// or 
LaraCache::updateAll(Article::class, 'latest');
```

## Config Properties

| method            | Type                     | description                                                                                                                                                     |
|-------------------|--------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------|
| driver            | string (default: `null`) | The default mechanism for handling cache storage.<br>If you keep this option `null`, LaraCache will use the default cache storage from `config/cache.php`       |
| first-day-of-week | integer (default: `0`)   | In some regions, saturday is first day of the week and in another regions it may be different. you can change the first day of a week by changing this property |
| last-day-of-week  | integer (default: `6`)   | In some regions, friday is last day of the week and in another regions it may be different. you can change the last day of a week by changing this property     |
| queue             | bool (default: `false`)  | Sometimes caching process is very heavy, so you have to queue the process and do it in background.                                                              |

------

## License

This software is released under [The MIT License (MIT)](LICENSE).
