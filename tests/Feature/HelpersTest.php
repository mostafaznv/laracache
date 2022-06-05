<?php

use Carbon\Carbon;
use Mostafaznv\LaraCache\Utils\Helpers;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function() {
    testTime()->freeze('2022-05-17 12:43:34');
});


it('will calculate time to end of the week in seconds', function() {
    $timeToEnd = Helpers::timeToEndOfWeek();
    expect($timeToEnd)->toBe(386185);

    $timeToEnd = Helpers::timeToEndOfWeek(-20);
    expect($timeToEnd)->toBe(386185);

    $timeToEnd = Helpers::timeToEndOfWeek(2);
    expect($timeToEnd)->toBe(1595785);
});

it('will calculate time to end of the week when end of week is something custom', function() {
    config()->set('laracache.last-day-of-week', Carbon::FRIDAY);

    $timeToEnd = Helpers::timeToEndOfWeek();
    expect($timeToEnd)->toBe(299785);
});

it('will calculate time to end of the day in seconds', function() {
    $timeToEnd = Helpers::timeToEndOfDay();
    expect($timeToEnd)->toBe(40585);

    $timeToEnd = Helpers::timeToEndOfDay(-20);
    expect($timeToEnd)->toBe(40585);

    $timeToEnd = Helpers::timeToEndOfDay(4);
    expect($timeToEnd)->toBe(386185);
});
