<?php

namespace System\Router;

class RouteNamed
{
  private $route;

  public function __construct(array $route, ?string $default_name = 'global')
  {
    $route['name'] = $default_name ?? 'global';
    $this->route = $route;
  }

  public function name(string $name)
  {
    $this->route['name'] =$name;
  }

  public function __destruct()
  {
    Router::addRoutes($this->route);
  }
}
