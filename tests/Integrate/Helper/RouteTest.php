<?php

declare(strict_types=1);

namespace System\Test\Integrate\Helper;

use PHPUnit\Framework\TestCase;
use System\Http\Request;
use System\Integrate\Application;
use System\Router\Router;

final class RouteTest extends TestCase
{
    private function getRespone(string $method, string $url)
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI']    = $url;

        ob_start();
        Router::run('/');

        return ob_get_clean();
    }

    /**
     * @test
     */
    public function itcanRidirectRoute(): void
    {
        new Application('/');
        app()->set(Request::class, new Request('/test'));
        Router::Reset();
        Router::get('/test', function () {
            return redirect('route.test2');
        })->name('route.test');

        Router::get('/test2', function () {
            echo 'supafast';
        })->name('route.test2');

        $route_basic  = $this->getRespone('get', '/test');

        $this->assertEquals('supafast', $route_basic);
    }
}
