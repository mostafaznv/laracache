<?php

use Mostafaznv\LaraCache\DTOs\CommandData;
use Mostafaznv\LaraCache\Exceptions\EntityIsNotAllowed;
use Mostafaznv\LaraCache\Exceptions\ModelDoesntUseLaraCacheTrait;
use Mostafaznv\LaraCache\Exceptions\ModelDoestNotExist;
use Mostafaznv\LaraCache\Exceptions\ModelOptionIsRequired;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel2;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestUser;


it('will throw ModelOptionIsRequired when no models are provided', function () {
    CommandData::make([], ['latest']);

})->throws(ModelOptionIsRequired::class);

it('throws EntityIsNotAllowed when entities are provided with multiple models', function () {
    CommandData::make(['Article', 'Product'], ['latest']);

})->throws(EntityIsNotAllowed::class);

it('throws ModelDoestNotExist when the specified model cannot be resolved', function () {
    CommandData::make(['Article']);

})->throws(ModelDoestNotExist::class);

it('throws ModelDoesntUseLaraCacheTrait when the model does not use the LaraCache trait', function () {
    CommandData::make([TestUser::class]);

})->throws(ModelDoesntUseLaraCacheTrait::class);

it('populates the models property with validated model class names', function () {
    $data = CommandData::make([TestModel::class, TestModel2::class]);

    expect($data->models)
        ->toBeArray()
        ->toHaveCount(2)
        ->toMatchArray([
            TestModel::class, TestModel2::class
        ]);
});

it('populates the entities property with the provided entities', function () {
    $entities = ['latest', 'featured', 'popular'];
    $data = CommandData::make([TestModel::class], $entities);

    expect($data->entities)
        ->toBeArray()
        ->toHaveCount(3)
        ->toMatchArray($entities);
});
