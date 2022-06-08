<?php

if (!function_exists('week_ending_seconds')) {
    /**
     * Calculate time to end of the week in seconds
     *
     * @param int $weeks
     * @return int
     */
    function week_ending_seconds(int $weeks = 0): int
    {
        $weeks = max($weeks, 0);

        $now = now();
        $end = $now->clone()->addWeeks($weeks);
        $end = $end->endOfWeek(config('laracache.last-day-of-week'));

        return $end->endOfDay()->diffInSeconds($now);
    }
}

if (!function_exists('day_ending_seconds')) {
    /**
     * Calculate time to end of the day in seconds
     *
     * @param int $days
     * @return int
     */
    function day_ending_seconds(int $days = 0): int
    {
        $days = max($days, 0);

        $now = now();
        $end = $now->clone()->addDays($days);

        return $end->endOfDay()->diffInSeconds($now);
    }
}
