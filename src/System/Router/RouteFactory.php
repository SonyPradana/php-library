<?php

namespace System\Router;

class RouteFactory
{
  /**
   * @var string Prefix of router expression
   */
  private $prefix;

  public function __construct(string $prefix)
  {
    $this->prefix = $prefix;
  }

  /**
   * Adding router prefix
   *
   * @param callable $callable Function to add prefix (use parrameter as RouteProvider)
   * @return this Chain Function
   */
  public function routes($callback)
  {
    $routes = new RouteProvider();
    call_user_func_array($callback, [$routes]);

    foreach ($routes->getRoutes() as $route) {
      $route['expression'] = $this->prefix . $route['expression'];

      Router::addRoutes($route);
    }

    return $this;
  }

}
