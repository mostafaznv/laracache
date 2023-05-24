<?php

use Illuminate\Support\Facades\Cache;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel2;

beforeEach(function() {
    config()->set('queue.default', 'sync');

    createModel();
    createModel2();
});


it('will update the list of models which use laracache', function() {
    $models = Cache::get('laracache.models');

    expect($models)
        ->toBeArray()
        ->toHaveCount(2)
        ->toMatchArray([
            TestModel::class,
            TestModel2::class
        ]);
});

it('wont duplicate models', function() {
    createModel();
    $models = Cache::get('laracache.models');

    expect($models)->toBeArray()->toHaveCount(2);
});

