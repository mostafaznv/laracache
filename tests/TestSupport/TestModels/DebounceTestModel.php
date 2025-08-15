<?php

namespace Mostafaznv\LaraCache\Tests\TestSupport\TestModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mostafaznv\LaraCache\CacheEntity;
use Mostafaznv\LaraCache\Traits\LaraCache;


class DebounceTestModel extends Model
{
    use LaraCache, SoftDeletes;

    protected $table = 'test_models';
    protected $guarded = [];

    public static function cacheEntities(): array
    {
        return [
            CacheEntity::make('latest.debounce')
                ->validForRestOfDay()
                ->shouldDebounce()
                ->setDefault(-1)
                ->cache(function() {
                    return DebounceTestModel::query()->latest()->first();
                }),

            CacheEntity::make('latest')
                ->validForRestOfDay()
                ->setDefault(-1)
                ->cache(function() {
                    return DebounceTestModel::query()->latest()->first();
                })
        ];
    }

}
