<?php

declare(strict_types=1);

namespace System\Support;

use System\Support\Exceptions\MacroNotFound;

trait Marco
{
    /**
     * List registered macro.
     *
     * @var string[]
     */
    protected static $macros = [];

    /**
     * Register string macro.
     *
     * @param string   $macro_name Method name
     * @param callable $call_back  Method call able
     *
     * @return void
     */
    public static function macro(string $macro_name, $call_back)
    {
        self::$macros[$macro_name] = $call_back;
    }

    /**
     * Call macro.
     *
     * @param string             $method     Method name
     * @param array<int, string> $parameters Parameters
     *
     * @return mixed
     *
     * @throw MacroNotFound
     */
    public static function __callStatic(string $method, array $parameters)
    {
        if (!array_key_exists($method, self::$macros)) {
            throw new MacroNotFound($method);
        }

        /** @var \Closure */
        $macro = static::$macros[$method];

        if ($macro instanceof \Closure) {
            $macro = $macro->bindTo(null, static::class);
        }

        return $macro(...$parameters);
    }

    /**
     * Call macro.
     *
     * @param string             $method     Method name
     * @param array<int, string> $parameters Parameters
     *
     * @return mixed
     *
     * @throw MacroNotFound
     */
    public function __call(string $method, array $parameters)
    {
        if (!array_key_exists($method, self::$macros)) {
            throw new MacroNotFound($method);
        }

        /** @var \Closure */
        $macro = static::$macros[$method];

        if ($macro instanceof \Closure) {
            $macro = $macro->bindTo($this, static::class);
        }

        return $macro(...$parameters);
    }

    /**
     * Cek macro already register.
     *
     * @param string $macro_name Macro name
     *
     * @return bool True if macro has register
     */
    public static function hasMacro(string $macro_name)
    {
        return array_key_exists($macro_name, self::$macros);
    }

    /**
     * Reset registered macro.
     *
     * @return void
     */
    public static function resetMacro()
    {
        self::$macros = [];
    }
}
