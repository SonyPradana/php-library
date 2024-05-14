<?php

declare(strict_types=1);

if (!function_exists('now')) {
    /**
     * Get time object class.
     *
     * @param string $date_format Set current time
     * @param string $time_zone   Set timezone
     *
     * @return System\Time\Now
     */
    function now(string $date_format = 'now', ?string $time_zone = null)
    {
        return new System\Time\Now($date_format, $time_zone);
    }
}
