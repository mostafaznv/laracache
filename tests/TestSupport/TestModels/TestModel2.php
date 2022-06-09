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
            CacheEntity::make('list.forever')
                ->forever()
                ->cache(function() {
                    return TestModel::query()->latest()->get();
                }),

            CacheEntity::make('list.day')
                ->validForRestOfDay()
                ->cache(function() {
                    return TestModel::query()->latest()->get();
                }),

            CacheEntity::make('list.week')
                ->validForRestOfWeek()
                ->cache(function() {
                    return TestModel::query()->latest()->get();
                }),

            CacheEntity::make('list.ttl')
                ->ttl(120)
                ->cache(function() {
                    return TestModel::query()->latest()->get();
                }),

            CacheEntity::make('latest')
                ->forever()
                ->cache(function() {
                    return TestModel::query()->latest()->first();
                })
        ];
    }

}
