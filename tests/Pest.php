<?php

use Mostafaznv\LaraCache\Tests\TestCase;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\DebounceTestModel;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\QueueTestModel;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\QueueTestModel2;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel2;


uses(TestCase::class)->in(__DIR__);


function createModel(?string $name = null): TestModel
{
    $model = new TestModel;
    $model->name = $name ?? 'test-name';
    $model->content = 'content';
    $model->save();

    return $model;
}

function createModel2(?string $name = null): TestModel2
{
    $model = new TestModel2();
    $model->name = $name ?? 'test-name-2';
    $model->content = 'content';
    $model->save();

    return $model;
}

function createQueueModel(?string $name = null): QueueTestModel
{
    $model = new QueueTestModel();
    $model->name = $name ?? 'queue-test-name';
    $model->content = 'content';
    $model->save();

    return $model;
}

function createQueueModel2(?string $name = null): QueueTestModel2
{
    $model = new QueueTestModel2();
    $model->name = $name ?? 'queue-test-name2';
    $model->content = 'content';
    $model->save();

    return $model;
}

function createDebounceModel(?string $name = null): DebounceTestModel
{
    $model = new DebounceTestModel;
    $model->name = $name ?? 'debounce-test-name';
    $model->content = 'content';
    $model->save();

    return $model;
}
