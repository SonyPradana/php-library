<?php

declare(strict_types=1);

namespace System\Test\Integrate\Http\Middleware;

use PHPUnit\Framework\TestCase;
use System\Http\Request;
use System\Http\Response;
use System\Integrate\Application;
use System\Integrate\Http\Exception\HttpException;
use System\Integrate\Http\Middleware\MaintenanceMiddleware;

final class PreventRequestInMaintenanceTest extends TestCase
{
    /**
     * @test
     */
    public function itCanPreventRequestDuringMaintenance()
    {
        $app        = new Application(__DIR__);
        $middleware = new MaintenanceMiddleware($app);
        $response   = new Response('test');
        $handle     = $middleware->handle(new Request('/'), fn (Request $request) => $response);

        $this->assertEquals($handle, $response);
    }

    /**
     * @test
     */
    public function itCanRedirectRequestDuringMaintenance()
    {
        $app        = new Application(dirname(__DIR__, 2));
        $app->setStoragePath(DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'storage2' . DIRECTORY_SEPARATOR);

        $middleware = new MaintenanceMiddleware($app);
        $response   = new Response('test');
        $handle     = $middleware->handle(new Request('/'), fn (Request $request) => $response);

        $this->assertEquals('/test', $handle->headers->get('Location'));
    }

    /**
     * @test
     */
    public function itCanRenderAndRetryRequestDuringMaintenance()
    {
        $app        = new Application(dirname(__DIR__, 2));
        $app->setStoragePath(DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'storage3' . DIRECTORY_SEPARATOR);

        $middleware = new MaintenanceMiddleware($app);
        $response   = new Response('test');
        $handle     = $middleware->handle(new Request('/'), fn (Request $request) => $response);

        $this->assertEquals('<h1>Test</h1>', $handle->getContent());
        $this->assertEquals(15, $handle->headers->get('Retry-After'));
    }

    /**
     * @test
     */
    public function itCanThrowRequestDuringMaintenance()
    {
        $app        = new Application(dirname(__DIR__, 2));
        $app->setStoragePath(DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR);

        $middleware = new MaintenanceMiddleware($app);
        $response   = new Response('test');

        $this->expectException(HttpException::class);
        $middleware->handle(new Request('/'), fn (Request $request) => $response);
    }
}
