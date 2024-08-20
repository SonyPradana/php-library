<?php

declare(strict_types=1);

namespace System\Console;

use System\Console\Style\Alert;
use System\Console\Style\Style;
use System\Console\Traits\TerminalTrait;

if (!function_exists('style')) {
    /**
     * Render text with terminal style (chain way).
     *
     * @param string $text
     *
     * @return Style
     */
    function style($text)
    {
        return new Style($text);
    }
}

if (!function_exists('info')) {
    /**
     * Render alert info.
     *
     * @param string $text
     *
     * @return Style
     */
    function info($text)
    {
        return Alert::render()->info($text);
    }
}

if (!function_exists('warn')) {
    /**
     * Render alert warn.
     *
     * @param string $text
     *
     * @return Style
     */
    function warn($text)
    {
        return Alert::render()->warn($text);
    }
}

if (!function_exists('fail')) {
    /**
     * Render alert fail.
     *
     * @param string $text
     *
     * @return Style
     */
    function fail($text)
    {
        return Alert::render()->fail($text);
    }
}

if (!function_exists('ok')) {
    /**
     * Render alert ok (success).
     *
     * @param string $text
     *
     * @return Style
     */
    function ok($text)
    {
        return Alert::render()->ok($text);
    }
}

if (!function_exists('option')) {
    /**
     * Command Prompt input option.
     *
     * @param string|Style            $title
     * @param array<string, callable> $options
     *
     * @return mixed
     */
    function option($title, array $options)
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
     *
     * @return mixed
     */
    function select($title, array $options)
    {
        return (new Prompt($title, $options))->select();
    }
}

if (!function_exists('text')) {
    /**
     * Command Prompt input text.
     *
     * @param string|Style $title
     *
     * @return mixed
     */
    function text($title, callable $callable)
    {
        return (new Prompt($title))->text($callable);
    }
}

if (!function_exists('password')) {
    /**
     * Command Prompt input password.
     *
     * @param string|Style $title
     *
     * @return mixed
     */
    function password($title, callable $callable, string $mask = '')
    {
        return (new Prompt($title))->password($callable, $mask);
    }
}

if (!function_exists('any_key')) {
    /**
     * Command Prompt detect any key.
     *
     * @param string|Style $title
     *
     * @return mixed
     */
    function any_key($title, callable $callable)
    {
        return (new Prompt($title))->anyKey($callable);
    }
}

if (!function_exists('width')) {
    /**
     * Get terminal width.
     */
    function width(int $min, int $max): int
    {
        $terminal = new class {
            use TerminalTrait;

            public function width(int $min, int $max): int
            {
                return $this->getWidth($min, $max);
            }
        };

        return $terminal->width($min, $max);
    }
}

if (!function_exists('exit_prompt')) {
    /**
     * Register ctrl+c event.
     *
     * @param string|Style            $title
     * @param array<string, callable> $options
     */
    function exit_prompt($title, ?array $options = null): void
    {
        $signal = defined('SIGINT') ? constant('SIGINT') : 2;
        $options ??= [
            'yes' => static function () use ($signal) {
                if (function_exists('posix_kill') && function_exists('posix_getpid')) {
                    posix_kill(posix_getgid(), $signal);
                }

                exit(128 + $signal);
            },
            'no'  => fn () => null,
        ];

        if (function_exists('sapi_windows_set_ctrl_handler') && 'cli' === PHP_SAPI) {
            sapi_windows_set_ctrl_handler(static function (int $event) use ($title, $options) {
                if (PHP_WINDOWS_EVENT_CTRL_C === $event) {
                    (new Style())->out();
                    (new Prompt($title, $options, 'no'))->option();
                }
            });
        }

        if (function_exists('pcntl_signal')) {
            pcntl_signal($signal, $options['yes']);
        }
    }
}

if (!function_exists('remove_exit_prompt')) {
    /**
     * Remove ctrl-c handle.
     */
    function remove_exit_prompt(): void
    {
        if (function_exists('sapi_windows_set_ctrl_handler') && 'cli' === PHP_SAPI) {
            sapi_windows_set_ctrl_handler(function (int $handler): void {}, false);
        }

        $signal  = defined('SIGINT') ? constant('SIGINT') : 2;
        $default = defined('SIG_DFL') ? constant('SIG_DFL') : 0;
        if (function_exists('pcntl_signal')) {
            pcntl_signal($signal, $default);
        }
    }
}
