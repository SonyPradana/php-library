<?php

namespace System\Router;

use Closure;


class RouteGroup
{
  private $setup;
  private $cleanup;

  public function __construct(Closure $setup, Closure $cleanup)
  {
    $this->setup = $setup;
    $this->cleanup = $cleanup;
  }

  public function group(Closure $callback)
  {
    call_user_func($this->setup);
    call_user_func($callback);
    call_user_func($this->cleanup);
  }
}
