<?php

use Mostafaznv\LaraCache\Tests\TestCase;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel;

uses(TestCase::class)->in(__DIR__);

function createModel(?string $name = null): TestModel
{
    $model = new TestModel;
    $model->name = $name ?? 'test-name';
    $model->content = 'content';
    $model->save();

    return $model;
}
