<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\Http\Request;
use System\Http\Response;
use System\Integrate\Application;
use System\Integrate\Http\Karnel;

final class KarnelTest extends TestCase
{
    private Application $app;
    private $karnel;

    protected function setUp(): void
    {
        $this->app = new Application('/');

        $this->app->set(
            Karnel::class,
            fn () => new $this->karnel($this->app)
        );

        $this->karnel = new class($this->app) extends Karnel {
            protected function dispatcher(Request $request): array
            {
                return [
                    'callable'   => fn () => new Response('ok', 200),
                    'parameters' => [],
                    'middleware' => [
                        new class() {
                            public function handle(Request $request, Closure $next): Response
                            {
                                return $next($request);
                            }
                        },
                        new class() {
                            public function handle(Request $request, Closure $next): Response
                            {
                                return new Response('redirect', 303);
                            }
                        },
                        new class() {
                            public function handle(Request $request, Closure $next): Response
                            {
                                if ($respone = $next($request)) {
                                    return $respone;
                                }

                                return new Response('forbidden', 403);
                            }
                        },
                    ],
                ];
            }
        };
    }

    protected function tearDown(): void
    {
        $this->app->flush();
        $this->karnel = null;
    }

    /** @test */
    public function itCanRedirectByMiddleware()
    {
        $respone = $this->app->make(Karnel::class);
        $test    = $respone->handle(
            new Request('test')
        );

        $this->assertEquals(
            'HTTP/1.1 303 ok' . "\r\n" .
            "\r\n" .
            "\r\n" .
            'redirect',
            $test->__toString()
        );
    }

    /** @test */
    public function itCanBootstrap()
    {
        $this->assertFalse($this->app->isBootstrapped());
        $this->app->make(Karnel::class)->bootstrap();
        $this->assertTrue($this->app->isBootstrapped());
    }
}
