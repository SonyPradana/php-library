<?php

declare(strict_types=1);

namespace System\Text;

if (!function_exists('string')) {
    /**
     * String manipulation.
     *
     * @param string $text Text string
     *
     * @return Text
     */
    function string($text)
    {
        return new Text($text);
    }
}

if (!function_exists('text')) {
    /**
     * String manipulation.
     *
     * @param string $text Text string
     *
     * @return Text
     */
    function text($text)
    {
        return new Text($text);
    }
}
