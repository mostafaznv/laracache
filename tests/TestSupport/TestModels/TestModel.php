<?php

namespace Mostafaznv\LaraCache\Tests\TestSupport\TestModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mostafaznv\LaraCache\CacheEntity;
use Mostafaznv\LaraCache\Traits\LaraCache;

class TestModel extends Model
{
    use LaraCache, SoftDeletes;

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
                }),

            CacheEntity::make('latest.no-create')
                ->refreshAfterCreate(false)
                ->cache(function() {
                    return TestModel::query()->latest()->first();
                }),

            CacheEntity::make('latest.no-update')
                ->refreshAfterUpdate(false)
                ->cache(function() {
                    return TestModel::query()->latest()->first();
                }),

            CacheEntity::make('latest.no-delete')
                ->refreshAfterDelete(false)
                ->cache(function() {
                    return TestModel::query()->latest()->first();
                }),

            CacheEntity::make('latest.no-restore')
                ->refreshAfterRestore(false)
                ->cache(function() {
                    return TestModel::query()->latest()->first();
                }),

            CacheEntity::make('empty.number')
                ->setDefault('empty value')
                ->cache(fn() => 0),

            CacheEntity::make('empty.array')
                ->setDefault('empty value')
                ->cache(fn() => []),

            CacheEntity::make('empty.string')
                ->setDefault('empty value')
                ->cache(fn() => ''),

            CacheEntity::make('empty.bool')
                ->setDefault('empty value')
                ->cache(fn() => false),

            CacheEntity::make('empty.null')
                ->setDefault('empty value')
                ->cache(fn() => null),

            CacheEntity::make('static.number')
                ->setDefault('default-value')
                ->cache(fn() => 1),

            CacheEntity::make('static.array')
                ->cache(fn() => [1, 2]),

            CacheEntity::make('static.bool')
                ->cache(fn() => true)
        ];
    }

}
