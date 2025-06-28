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
     * This test contain test for middleware and
     * test for register request attribute.
     *
     * @test
     */
    public function itCanHandleMiddlewareReserve(): void
    {
        $middleware = [
            new class {
                public function handle(Request $request, Closure $next): Response
                {
                    echo 'middeleware A.before';
                    $next($request);
                    echo 'middeleware A.after';

                    return $next($request);
                }
            },
            new class {
                public function handle(Request $request, Closure $next): Response
                {
                    echo 'middeleware B.before';
                    $next($request);
                    echo 'middeleware B.after';

                    return $next($request);
                }
            },
        ];
        $dispatcher = [
            'callable' => function ($param) {
                echo $param;

                return new Response('');
            },
            'parameters' => [
                'param' => 'final response',
            ],
        ];
        ob_start();
        $pipe = (fn () => $this->{'middlewarePipeline'}($middleware, $dispatcher))->call($this->app[Kernel::class]);
        $pipe(new Request('/'));
        $out = ob_get_clean();

        $this->assertEquals(
            'middeleware A.beforemiddeleware B.beforefinal responsemiddeleware B.afterfinal responsemiddeleware A.aftermiddeleware B.beforefinal responsemiddeleware B.afterfinal response',
            $out
        );
    }
}
