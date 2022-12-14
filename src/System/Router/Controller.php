<?php

namespace System\Router;

abstract class Controller
{
    public $resource_map = [
        'index' => 'index',
        'store' => 'store',
    ];

    public function __invoke($invoke)
    {
        call_user_func([$this, $invoke]);
    }

    public static function renderView(string $view_path, array $portal = [])
    {
        // overwrite
    }

    /**
     * @var static This classs
     */
    private self $_static;

    /**
     * Instance of controller.
     * Shorthadn to crete new class.
     */
    public static function static()
    {
        /* @phpstan-ignore-next-line */
        return self::$_static = self::$_static ?? new static();
    }
}
