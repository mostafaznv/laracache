<?php

namespace Mostafaznv\LaraCache\Tests\TestSupport\TestModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mostafaznv\LaraCache\CacheEntity;
use Mostafaznv\LaraCache\Traits\LaraCache;

class TestModel2 extends Model
{
    use LaraCache, SoftDeletes;

    protected $table = 'test_models_2';
    protected $guarded = [];

    public static function cacheEntities(): array
    {
        return [
            CacheEntity::make('list-2.forever')
                ->forever()
                ->cache(function() {
                    return TestModel2::query()->latest()->get();
                }),

            CacheEntity::make('list-2.day')
                ->validForRestOfDay()
                ->cache(function() {
                    return TestModel2::query()->latest()->get();
                }),

            CacheEntity::make('list-2.week')
                ->validForRestOfWeek()
                ->cache(function() {
                    return TestModel2::query()->latest()->get();
                }),

            CacheEntity::make('list-2.ttl')
                ->ttl(120)
                ->cache(function() {
                    return TestModel2::query()->latest()->get();
                }),

            CacheEntity::make('latest-2')
                ->forever()
                ->cache(function() {
                    return TestModel2::query()->latest()->first();
                })
        ];
    }

}
