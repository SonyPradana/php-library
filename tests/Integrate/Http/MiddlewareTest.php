<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\Http\Request;
use System\Http\Response;
use System\Integrate\Application;
use System\Integrate\Http\Karnel as Kernel;
use System\Integrate\PackageManifest;

final class MiddlewareTest extends TestCase
{
    private Application $app;
    private ?Kernel $kernel;

    protected function setUp(): void
    {
        $this->app = new Application('/');

        // overwrite PackageManifest has been set in Application before.
        $this->app->set(PackageManifest::class, fn () => new PackageManifest(
            base_path: dirname(__DIR__) . '/assets/app2/',
            application_cache_path: dirname(__DIR__) . '/assets/app2/bootstrap/cache/',
            vendor_path: '/app2/package/'
        ));
        $this->kernel = new Kernel($this->app);

        $this->app->set(
            Kernel::class,
            fn () => $this->kernel
        );
    }

    protected function tearDown(): void
    {
        $this->app->flush();
        $this->kernel = null;
    }

    /**
     * Test middleware pipeline with a dispatcher that returns a response.
     *
     * @test
     */
    public function itCanHandleMiddlewareReserveabel(): void
    {
        $middleware = [
            new class {
                public function handle(Request $request, Closure $next): Response
                {
                    echo 'middeleware.A.before/';
                    $response =  $next($request);
                    echo 'middeleware.A.after/';

                    return $response;
                }
            },
            new class {
                public function handle(Request $request, Closure $next): Response
                {
                    echo 'middeleware.B.before/';

                    // skip reverseable middleware
                    return $next($request);
                }
            },
            new class {
                public function handle(Request $request, Closure $next): Response
                {
                    echo 'middeleware.C.before/';
                    $response = $next($request);
                    echo 'middeleware.C.after/';

                    return $response;
                }
            },
        ];
        $dispatcher = [
            'callable' => function ($param) {
                echo $param;

                return new Response('');
            },
            'parameters' => [
                'param' => 'final response/',
            ],
        ];
        ob_start();
        $pipe = (fn () => $this->{'middlewarePipeline'}($middleware, $dispatcher))->call($this->app[Kernel::class]);
        $pipe(new Request('/'));
        $out = ob_get_clean();

        $this->assertEquals(
            'middeleware.A.before/middeleware.B.before/middeleware.C.before/final response/middeleware.C.after/middeleware.A.after/',
            $out,
            'middleware must be called in order and reserveable'
        );
    }
}
