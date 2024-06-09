<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\Http\Request;
use System\Http\Response;
use System\Integrate\Application;
use System\Integrate\Exceptions\Handler;
use System\Integrate\Http\Exception\HttpException;
use System\Integrate\Http\Karnel;
use System\Integrate\PackageManifest;

final class KarnelHandleExceptionTest extends TestCase
{
    private Application $app;
    private Karnel $karnel;
    private Handler $handler;

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

        $this->app->set(
            Handler::class,
            fn () => $this->handler
        );

        $this->karnel = new class($this->app) extends Karnel {
            protected function dispatcher(Request $request): array
            {
                throw new HttpException(500, 'Test Exception');

                return [
                    'callable'   => fn () => new Response('ok', 200),
                    'parameters' => [],
                    'middleware' => [],
                ];
            }
        };

        $this->handler = new class($this->app) extends Handler {
            public function render(Request $request, Throwable $th): Response
            {
                return new Response($th->getMessage(), 500);
            }
        };
    }

    protected function tearDown(): void
    {
        $this->app->flush();
    }

    /** @test */
    public function itCanRenderException()
    {
        $karnel      = $this->app->make(Karnel::class);
        $response    = $karnel->handle(new Request('/test'));

        $this->assertEquals('Test Exception', $response->getContent());
        $this->assertEquals(500, $response->getStatusCode());
    }
}
