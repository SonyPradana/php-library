<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\Router\Route;
use System\Router\RouteDispatcher;
use System\Router\Router;

class RouteDispatchTest extends TestCase
{
    protected function tearDown(): void
    {
        Router::Reset();
    }

    private function routes()
    {
        return [
            new Route([
                'method'     => 'GET',
                'expression' => '/',
                'function'   => fn () => true,
            ]),
        ];
    }

    /** @test */
    public function itCanResultCurrentRoute()
    {
        $dispatcher = RouteDispatcher::dispatchFrom('/', 'GET', $this->routes());

        $dispatch = $dispatcher->run(
            fn ($callable, $params) => call_user_func_array($callable, $params),
            fn ($path)          => 'not found - ',
            fn ($path, $method) => 'method not allowd - - ',
        );

        $real_route               = $this->routes()[0];
        $real_route['expression'] = '^/$';
        $this->assertEquals($real_route, $dispatcher->current());
    }

    /** @test */
    public function itCanDispatchAndCall()
    {
        $dispatcher = RouteDispatcher::dispatchFrom('/', 'GET', $this->routes());

        $dispatch = $dispatcher->run(
            fn ($callable, $params) => call_user_func_array($callable, $params),
            fn ($path)          => 'not found - ',
            fn ($path, $method) => 'method not allowd - - ',
        );

        $result = call_user_func_array($dispatch['callable'], $dispatch['params']);

        $this->assertEquals(true, $result);
    }

    /** @test */
    public function itCanDispatchAndRunFound()
    {
        $dispatcher = RouteDispatcher::dispatchFrom('/', 'GET', $this->routes());

        $dispatch = $dispatcher->run(
            fn () => 'found',
            fn ($path)          => 'not found - ',
            fn ($path, $method) => 'method not allowd - - ',
        );

        $result = call_user_func_array($dispatch['callable'], $dispatch['params']);

        $this->assertEquals('found', $result);
    }

    /** @test */
    public function itCanDispatchAndRunNotFound()
    {
        $dispatcher = RouteDispatcher::dispatchFrom('/not-found', 'GET', $this->routes());

        $dispatch = $dispatcher->run(
            fn () => 'found',
            fn ($path)          => 'not found - ',
            fn ($path, $method) => 'method not allowd - - ',
        );

        $result = call_user_func_array($dispatch['callable'], $dispatch['params']);

        $this->assertEquals('not found - ', $result);
    }

    /** @test */
    public function itCanDispatchAndRunMethodNotAllowed()
    {
        $dispatcher = RouteDispatcher::dispatchFrom('/', 'POST', $this->routes());

        $dispatch = $dispatcher->run(
            fn () => 'found',
            fn ($path)          => 'not found - ',
            fn ($path, $method) => 'method not allowd - - ',
        );

        $result = call_user_func_array($dispatch['callable'], $dispatch['params']);

        $this->assertEquals('method not allowd - - ', $result);
    }
}
