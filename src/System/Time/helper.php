<?php

if (!function_exists('now')) {
    /**
     * Get time object class.
     *
     * @param string $date_format Set current time
     */
    function now(string $date_format = 'now')
    {
        return new \System\Time\Now($date_format);
    }
}
