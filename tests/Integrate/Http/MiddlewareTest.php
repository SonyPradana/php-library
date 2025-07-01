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

        $this->app->set(Kernel::class, fn () => $this->kernel);
    }

    protected function tearDown(): void
    {
        $this->app->flush();
        $this->kernel = null;
    }

    /**
     * Test middleware pipeline with a dispatcher that returns a response using function.
     *
     * @test
     *
     * @covers \System\Integrate\Http\Karnel::middlewarePipeline
     * @covers \System\Integrate\Http\Karnel::executeMiddleware
     */
    public function itCanHandleMiddlewareReversibleUsingClassString(): void
    {
        $middleware = [
            ClassA::class,
            ClassB::class,
            ClassC::class,
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
            'middleware.A.before/middleware.B.before/middleware.C.before/final response/middleware.C.after/middleware.A.after/',
            $out,
            'middleware must be called in order and reversible using function'
        );
    }

    /**
     * Test middleware pipeline with a dispatcher that returns a response using function.
     *
     * @test
     *
     * @covers \System\Integrate\Http\Karnel::middlewarePipeline
     * @covers \System\Integrate\Http\Karnel::executeMiddleware
     */
    public function itThrowInvalidArgumentMethodNotFound(): void
    {
        $middleware = [ClassD::class];
        $dispatcher = [
            'callable' => function ($param) {
                echo $param;

                return new Response($param);
            },
            'parameters' => [
                'param' => 'final response/',
            ],
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Middleware must be a class with handle method');

        $pipe = (fn () => $this->{'middlewarePipeline'}($middleware, $dispatcher))->call($this->app[Kernel::class]);
        $pipe(new Request('/'));
    }
}

// code below is the middleware classes used in the tests

class ClassA
{
    public function handle(Request $request, Closure $next): Response
    {
        echo 'middleware.A.before/';
        $response =  $next($request);
        echo 'middleware.A.after/';

        return $response;
    }
}

class ClassB
{
    public function handle(Request $request, Closure $next): Response
    {
        echo 'middleware.B.before/';

        // skip reversible middleware
        return $next($request);
    }
}

class ClassC
{
    public function handle(Request $request, Closure $next): Response
    {
        echo 'middleware.C.before/';
        $response = $next($request);
        echo 'middleware.C.after/';

        return $response;
    }
}

class ClassD
{
    public function __invoke(Request $request, Closure $next): Response
    {
        echo 'middleware.D.before/';
        $response = $next($request);
        echo 'middleware.D.after/';

        return $response;
    }
}
