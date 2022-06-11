<?php

use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel;

it('will return default value if cache content is zero', function() {
    $cache = TestModel::cache()->get('empty.number');

    expect($cache)->toBe('empty value');
});

it('will return default value if cache content is an empty array', function() {
    $cache = TestModel::cache()->get('empty.array');

    expect($cache)->toBe('empty value');
});

it('will return default value if cache content is an empty string', function() {
    $cache = TestModel::cache()->get('empty.string');

    expect($cache)->toBe('empty value');
});

it('will return default value if cache content is false', function() {
    $cache = TestModel::cache()->get('empty.bool');

    expect($cache)->toBe('empty value');
});

it('will return default value if cache content is null', function() {
    $cache = TestModel::cache()->get('empty.null');

    expect($cache)->toBe('empty value');
});
