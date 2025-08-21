<?php

declare(strict_types=1);

namespace System\Test\Integrate\Http\Middleware;

use PHPUnit\Framework\TestCase;
use System\Cache\Storage\ArrayStorage;
use System\Http\Request;
use System\Http\Response;
use System\Integrate\Http\Middleware\ThrottleMiddleware;
use System\RateLimiter\RateLimiter;
use System\RateLimiter\RateLimiter\FixedWindow;

final class ThrottleMiddlewareTest extends TestCase
{
    /**
     * @test
     */
    public function itCanThrottleRequest()
    {
        $limiter    = new RateLimiter(new FixedWindow(new ArrayStorage(), 60, 1));
        $middleware = new ThrottleMiddleware($limiter);
        $request    = new Request('/');

        // Simulate 60 requests to trigger throttling
        for ($i = 0; $i < 60; $i++) {
            $middleware->handle($request, fn (Request $request) => new Response(''));
        }

        $response = $middleware->handle($request, fn (Request $request) => new Response(''));

        $this->assertEquals(429, $response->getStatusCode());
        $this->assertEquals('Too Many Requests', $response->getContent());
        $this->assertEquals('60', $response->headers->get('X-RateLimit-Limit'));
        $this->assertEquals('0', $response->headers->get('X-RateLimit-Remaining'));
    }

    /**
     * @test
     */
    public function itCanPassRequest()
    {
        $limiter    = new RateLimiter(new FixedWindow(new ArrayStorage(), 60, 1));
        $middleware = new ThrottleMiddleware($limiter);
        $request    = new Request('/');

        // Simulate 59 requests, so one remaining
        for ($i = 0; $i < 58; $i++) {
            $middleware->handle($request, fn (Request $request) => new Response(''));
        }

        $response = $middleware->handle($request, fn (Request $request) => new Response(''));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('', $response->getContent());
        $this->assertEquals('60', $response->headers->get('X-RateLimit-Limit'));
        $this->assertEquals('1', $response->headers->get('X-RateLimit-Remaining'));
    }
}
