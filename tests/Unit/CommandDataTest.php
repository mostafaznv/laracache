<?php

use Mostafaznv\LaraCache\DTOs\CommandData;
use Mostafaznv\LaraCache\Exceptions\EntityIsNotAllowed;
use Mostafaznv\LaraCache\Exceptions\ModelDoesntUseLaraCacheTrait;
use Mostafaznv\LaraCache\Exceptions\ModelDoestNotExist;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel2;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestUser;


it('will throw an exception if we passed some entities with more than one model to the DTO', function() {
    CommandData::make(['Article', 'Product'], ['latest']);
})->throws(EntityIsNotAllowed::class);

it('will throw an exception if model does not exist', function() {
    CommandData::make(['Article']);
})->throws(ModelDoestNotExist::class);

it('will throw an exception if model does not use LaraCache trait', function() {
    CommandData::make([TestUser::class]);
})->throws(ModelDoesntUseLaraCacheTrait::class);

it('will assign existing models to models property', function() {
    $data = CommandData::make([TestModel::class, TestModel2::class]);

    expect($data->models)
        ->toBeArray()
        ->toHaveCount(2)
        ->toMatchArray([
            TestModel::class, TestModel2::class
        ]);
});

it('will assign entities to entities property', function() {
    $entities = ['latest', 'featured', 'popular'];
    $data = CommandData::make([TestModel::class], $entities);

    expect($data->entities)
        ->toBeArray()
        ->toHaveCount(3)
        ->toMatchArray($entities);
});
