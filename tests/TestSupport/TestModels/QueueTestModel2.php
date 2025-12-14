<?php

namespace Mostafaznv\LaraCache\Tests\TestSupport\TestModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mostafaznv\LaraCache\CacheEntity;
use Mostafaznv\LaraCache\Traits\LaraCache;


class QueueTestModel2 extends Model
{
    use LaraCache, SoftDeletes;

    protected $table   = 'test_models';
    protected $guarded = [];


    public static function cacheEntities(): array
    {
        return [
            CacheEntity::make('latest')
                ->validForRestOfDay()
                ->isQueueable()
                ->setDefault(-1)
                ->refreshAfterCreate(false)
                ->cache(function () {
                    return QueueTestModel2::query()->latest()->first();
                })
        ];
    }

}
