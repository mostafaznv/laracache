<?php

use Mostafaznv\LaraCache\DTOs\CacheStatus;


it('can determine if two cache status are equal', function() {
    $isEqual = CacheStatus::CREATED()->equals(CacheStatus::CREATED());
    expect($isEqual)->toBeTrue();

    $isEqual = CacheStatus::CREATED()->equals(CacheStatus::DELETED());
    expect($isEqual)->toBeFalse();
});

it('can get value of cache status', function(CacheStatus $status, string $value) {
    expect($status->getValue())->toBe($value);
})->with([
    'NOT_CREATED' => ['status' => CacheStatus::NOT_CREATED(), 'value' => 'NOT_CREATED'],
    'CREATING'    => ['status' => CacheStatus::CREATING(), 'value' => 'CREATING'],
    'CREATED'     => ['status' => CacheStatus::CREATED(), 'value' => 'CREATED'],
    'DELETED'     => ['status' => CacheStatus::DELETED(), 'value' => 'DELETED'],
]);
