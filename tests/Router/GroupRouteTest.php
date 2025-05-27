<?php

use PHPUnit\Framework\TestCase;
use System\Http\Request;
use System\Router\RouteDispatcher;
use System\Router\Router;

class GroupRouteTest extends TestCase
{
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

    /**
     * @test
     */
    public function itCanMakeCostumeRouteGroup()
    {
        Router::group([
            'prefix' => '/test',
        ], function () {
            Router::get('/foo', [SomeClass::class, 'foo']);
        });
        Router::get('/bar', [SomeClass::class, 'bar']);

        $res = $this->dispatcher('/test/foo', 'get');
        $this->assertEquals('bar', $res);

        $res = $this->dispatcher('/bar', 'get');
        $this->assertEquals('foo', $res);
    }

    /**
     * @test
     */
    public function itCanUseGroupController()
    {
        Router::controller(SomeClass::class)->group(function () {
            Router::get('/foo', 'foo');
            Router::get('/bar', 'bar');
        });

        $res = $this->dispatcher('/foo', 'get');
        $this->assertEquals('bar', $res);

        $res = $this->dispatcher('/bar', 'get');
        $this->assertEquals('foo', $res);
    }

    /**
     * @test
     */
    public function itCanHandleNestedPrefixes()
    {
        Router::prefix('/api')->group(function () {
            Router::get('/status', function () {
                echo 'api-status';
            });

            Router::prefix('/v1')->group(function () {
                Router::get('/users', function () {
                    echo 'api-v1-users';
                });

                Router::prefix('/admin')->group(function () {
                    Router::get('/dashboard', function () {
                        echo 'api-v1-admin-dashboard';
                    });
                });
            });
        });

        $res = $this->dispatcher('/api/status', 'get');
        $this->assertEquals('api-status', $res);

        $res = $this->dispatcher('/api/v1/users', 'get');
        $this->assertEquals('api-v1-users', $res);

        $res = $this->dispatcher('/api/v1/admin/dashboard', 'get');
        $this->assertEquals('api-v1-admin-dashboard', $res);
    }
}

class SomeClass
{
    public function foo()
    {
        echo 'bar';
    }

    public function bar()
    {
        echo 'foo';
    }
}
