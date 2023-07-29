<?php

declare(strict_types=1);

namespace System\Router;

class RouteGroup
{
    /** @var callable */
    private $setup;
    /** @var callable */
    private $cleanup;

    public function __construct(\Closure $setup, \Closure $cleanup)
    {
        $this->setup   = $setup;
        $this->cleanup = $cleanup;
    }

    /**
     * @template T
     *
     * @param callable(): T $callback
     *
     * @return T
     */
    public function group($callback)
    {
        // call stack
        ($this->setup)();
        $result = ($callback)();
        ($this->cleanup)();

        return $result;
    }
}
