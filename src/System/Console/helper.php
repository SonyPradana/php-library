<?php

declare(strict_types=1);

namespace System\Console;

if (!function_exists('style')) {
    /**
     * Render text with terminal style (chain way);.
     *
     * @param string $text
     *
     * @return \System\Console\Style\Style
     */
    function style($text)
    {
        return new \System\Console\Style\Style($text);
    }
}
