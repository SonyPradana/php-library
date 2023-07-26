<?php

declare(strict_types=1);

namespace System\Test\Integrate\Router;

use PHPUnit\Framework\TestCase;
use System\Http\Request;
use System\Http\Response;
use System\Integrate\Application;
use System\Router\RouteDispatcher;
use System\Router\Router;

class RouteDispatchTest extends TestCase
{
    public function testDispatchRouterUsingContainer()
    {
        Router::get('/', BasicRouteClass::class);

        $app = new Application('/');
        $app->set(BasicRouteClass::class, fn () => new BasicRouteClass());
        $request    = new Request('/', [], [], [], [], [], [], 'get');
        $dispatcher = new RouteDispatcher($request, Router::getRoutesRaw());
        $dispatch   = $dispatcher->run(
            fn ($callable, $params) => $app->call($callable, $params),
            fn ($path) => new Response("path: {$path}", Response::HTTP_NOT_FOUND),
            fn ($path, $method) => new Response("path: {$path}, method {$method}", Response::HTTP_NOT_FOUND)
        );

        $response = $app->call($dispatch['callable'], $dispatch['params']);

        $this->assertEquals('ok', $response);
        $app->flush();
        Router::Reset();
    }
}

class BasicRouteClass
{
    public function __invoke()
    {
        return 'ok';
    }
}
