<?php

namespace System\Text;

if (!function_exists('string')) {
    function string($text)
    {
        return new Text($text);
    }

    function text($text)
    {
        return new Text($text);
    }
}
