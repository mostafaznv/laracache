<?php

namespace Mostafaznv\LaraCache\Utils;

use Carbon\Carbon;

class Helpers
{
    /**
     * Calculate time to end of the week in seconds
     *
     * @param int $weeks
     * @return int
     */
    public static function timeToEndOfWeek(int $weeks = 0): int
    {
        $weeks = max($weeks, 0);

        $now = Carbon::now();
        $end = $now->clone()->addWeeks($weeks);
        $end = $end->endOfWeek(config('laracache.last-day-of-week'));

        return $end->endOfDay()->diffInSeconds($now);
    }

    /**
     * Calculate time to end of the day in seconds
     *
     * @param int $days
     * @return int
     */
    public static function timeToEndOfDay(int $days = 0): int
    {
        $days = max($days, 0);

        $now = Carbon::now();
        $end = $now->clone()->addDays($days);

        return $end->endOfDay()->diffInSeconds($now);
    }
}
