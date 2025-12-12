<?php

if (!function_exists('week_ending_seconds')) {
    /**
     * Compute the number of seconds remaining until the end of the week.
     *
     * This function returns the number of seconds from the current time until the end
     * of the week. The weekend day is determined by the `laracache.last-day-of-week`
     * configuration value. An optional non-negative offset may be provided to calculate
     * the seconds until the end of a future week.
     *
     * @param int $weeks Non-negative number of weeks to offset the calculation.
     * @return int Seconds remaining until the end of the specified week.
     */
    function week_ending_seconds(int $weeks = 0): int
    {
        $weeks = max($weeks, 0);

        $now = now();
        $end = $now->clone()->addWeeks($weeks);
        $end = $end->endOfWeek(config('laracache.last-day-of-week'));

        return $end->endOfDay()->diffInSeconds($now, true);
    }
}

if (!function_exists('day_ending_seconds')) {
    /**
     * Compute the number of seconds remaining until the end of the day.
     *
     * This function returns the number of seconds from the current time until the end
     * of the day. An optional non-negative offset may be provided to calculate the
     * seconds until the end of a future day.
     *
     * @param int $days Non-negative number of days to offset the calculation.
     * @return int Seconds remaining until the end of the specified day.
     */
    function day_ending_seconds(int $days = 0): int
    {
        $days = max($days, 0);

        $now = now();
        $end = $now->clone()->addDays($days);

        return $end->endOfDay()->diffInSeconds($now, true);
    }
}
