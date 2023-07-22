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

    public function group(\Closure $callback): mixed
    {
        call_user_func($this->setup);
        $result = call_user_func($callback);
        call_user_func($this->cleanup);

        return $result;
    }
}
