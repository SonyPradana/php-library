<?php

namespace System\Router;

abstract class Controller
{
  public function __invoke($invoke)
  {
    call_user_func([$this, $invoke]);
  }

  public static function renderView($view_name, $portal)
  {
    // overwrite
  }

  /**
   * @var static This classs
   */
  private self $_static;

  /**
   * Instance of controller.
   * Shorthadn to crete new class
   */
  public static function static()
  {
    return self::$_static ?? new static;
  }
}
