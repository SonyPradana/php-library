<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\Http\Request;
use System\Router\RouteDispatcher;
use System\Router\Router;

class RouteControllerTest extends TestCase
{
    protected $backup;

    public function __construct()
    {
        parent::__construct();
        // autoload asset class
        require_once __DIR__ . '/Assests/RouteClassController.php';
    }

    protected function tearDown(): void
    {
        Router::Reset();
    }

    public function dispatcher(string $url, string $method)
    {
        $request  = new Request($url, [], [], [], [], [], [], $method);
        $dispatch = new RouteDispatcher($request, Router::getRoutesRaw());

        $call = $dispatch->run(
            // found
            function ($callable, $param) {
                if (is_array($callable)) {
                    [$class, $method] = $callable;

                    return call_user_func_array([new $class(), $method], $param);
                }

                return call_user_func($callable, $param);
            },
            // not found
            function ($path) { echo 'not found'; },
            // method not allowed
            function ($path, $method) { echo 'not allowed'; },
        );

        ob_start();
        call_user_func_array($call['callable'], $call['params']);

        return ob_get_clean();
    }

    // /** @test */
    // public function itCanRouteUsingControllerWithoutMethod()
    // {
    //     Router::get('/', RouteClassController::class);

    //     ob_start();
    //     Router::run('/');
    //     $out = ob_get_clean();

    //     $this->assertEquals('works', $out);
    // }

    /** @test */
    public function itCanRouteUsingResourceControllerIndex()
    {
        Router::resource('/', RouteClassController::class);

        $res = $this->dispatcher('/', 'get');

        $this->assertEquals('works', $res);
    }

    // /** @test */
    // public function itCanRouteUsingResourceControllerStore()
    // {
    //     $_SERVER['REQUEST_METHOD'] = '/';
    //     $_SERVER['REQUEST_METHOD'] = 'post';

    //     Router::resource('/', RouteClassController::class);

    //     ob_start();
    //     Router::run('/');
    //     $out = ob_get_clean();

    //     $this->assertEquals('works', $out);
    // }
}
