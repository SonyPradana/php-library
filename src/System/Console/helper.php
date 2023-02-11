<?php

declare(strict_types=1);

namespace System\Console;

use System\Console\Style\Style;

if (!function_exists('style')) {
    /**
     * Render text with terminal style (chain way).
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

if (!function_exists('info')) {
    /**
     * Render alert info.
     *
     * @param string $text
     *
     * @return \System\Console\Style\Style
     */
    function info($text)
    {
        return \System\Console\Style\Alert::render()->info($text);
    }
}

if (!function_exists('warn')) {
    /**
     * Render alert warn.
     *
     * @param string $text
     *
     * @return \System\Console\Style\Style
     */
    function warn($text)
    {
        return \System\Console\Style\Alert::render()->warn($text);
    }
}

if (!function_exists('fail')) {
    /**
     * Render alert fail.
     *
     * @param string $text
     *
     * @return \System\Console\Style\Style
     */
    function fail($text)
    {
        return \System\Console\Style\Alert::render()->fail($text);
    }
}

if (!function_exists('ok')) {
    /**
     * Render alert ok (success).
     *
     * @param string $text
     *
     * @return \System\Console\Style\Style
     */
    function ok($text)
    {
        return \System\Console\Style\Alert::render()->ok($text);
    }
}

if (!function_exists('option')) {
    /**
     * Command Prompt input option.
     *
     * @param string|Style            $title
     * @param array<string, callable> $options
     */
    function option($title, array $options): mixed
    {
        return (new Prompt($title, $options))->option();
    }
}

if (!function_exists('select')) {
    /**
     * Command Prompt input selection.
     *
     * @param string|Style            $title
     * @param array<string, callable> $options
     */
    function select($title, array $options): mixed
    {
        return (new Prompt($title, $options))->select();
    }
}

if (!function_exists('text')) {
    /**
     * Command Prompt input text.
     *
     * @param string|Style $title
     */
    function text($title, callable $callable): mixed
    {
        return (new Prompt($title))->text($callable);
    }
}
