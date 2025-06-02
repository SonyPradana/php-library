<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\Http\Request;
use System\Http\Response;
use System\Integrate\Application;
use System\Integrate\Http\Karnel;
use System\Integrate\PackageManifest;

final class KarnelTest extends TestCase
{
    private Application $app;
    private $karnel;

    protected function setUp(): void
    {
        $this->app = new Application('/');

        // overwrite PackageManifest has been set in Application before.
        $this->app->set(PackageManifest::class, fn () => new PackageManifest(
            base_path: dirname(__DIR__) . '/assets/app2/',
            application_cache_path: dirname(__DIR__) . '/assets/app2/bootstrap/cache/',
            vendor_path: '/app2/package/'
        ));

        $this->app->set(
            Karnel::class,
            fn () => new $this->karnel($this->app)
        );

        $this->karnel = new class($this->app) extends Karnel {
            protected function dispatcher(Request $request): array
            {
                return [
                    'callable'   => fn () => new Response('ok', 200),
                    'parameters' => [
                        'foo' => 'bar',
                    ],
                    'middleware' => [
                        new class {
                            public function handle(Request $request, Closure $next): Response
                            {
                                return $next($request);
                            }
                        },
                        new class {
                            public function handle(Request $request, Closure $next): Response
                            {
                                if ($request->getAttribute('foo', null) === 'bar') {
                                    return new Response('redirect', 303);
                                }

                                return $next($request);
                            }
                        },
                        new class {
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

    /**
     * This test contain test for middleware and
     * test for register request attribute.
     *
     * @test
     */
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
