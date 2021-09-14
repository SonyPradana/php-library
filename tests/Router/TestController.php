<?php

use System\Router\Controller;
use System\View\View;

class TestController extends Controller
{
  public static function renderView(string $view_path, array $portal = [])
  {
    $path = dirname(__DIR__) . "\Router\sample";
    View::render($path . $view_path, $portal);
  }
}
