<?php

use Carbon\CarbonInterface;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function() {
    testTime()->freeze('2022-05-17 12:43:34');
});


it('will calculate time to end of the week in seconds', function() {
    $timeToEnd = week_ending_seconds();
    expect($timeToEnd)->toBe(386185);

    $timeToEnd = week_ending_seconds(-20);
    expect($timeToEnd)->toBe(386185);

    $timeToEnd = week_ending_seconds(2);
    expect($timeToEnd)->toBe(1595785);
});

it('will calculate time to end of the week when end of week is something custom', function() {
    config()->set('laracache.last-day-of-week', CarbonInterface::FRIDAY);

    $timeToEnd = week_ending_seconds();
    expect($timeToEnd)->toBe(299785);
});

it('will calculate time to end of the day in seconds', function() {
    $timeToEnd = day_ending_seconds();
    expect($timeToEnd)->toBe(40585);

    $timeToEnd = day_ending_seconds(-20);
    expect($timeToEnd)->toBe(40585);

    $timeToEnd = day_ending_seconds(4);
    expect($timeToEnd)->toBe(386185);
});
