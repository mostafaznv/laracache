<?php

namespace Mostafaznv\LaraCache\Tests\TestSupport\TestModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mostafaznv\LaraCache\CacheEntity;
use Mostafaznv\LaraCache\Traits\LaraCache;

class QueueTestModel extends Model
{
    use LaraCache, SoftDeletes;

    protected $table = 'test_models';
    protected $guarded = [];

    public static function cacheEntities(): array
    {
        return [
            CacheEntity::make('latest')
                ->validForRestOfDay()
                ->isQueueable()
                ->setDefault(-1)
                ->cache(function() {
                    sleep(1);

                    return QueueTestModel::query()->latest()->first();
                })
        ];
    }

}
