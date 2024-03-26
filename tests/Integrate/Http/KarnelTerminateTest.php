<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\Http\Request;
use System\Http\Response;
use System\Integrate\Application;
use System\Integrate\Http\Karnel;

final class KarnelTerminateTest extends TestCase
{
    private $app;
    private $karnel;

    protected function setUp(): void
    {
        $this->app = new Application('/');

        $this->app->set(
            Karnel::class,
            fn () => new $this->karnel($this->app)
        );

        $this->karnel = new class($this->app) extends Karnel {
            public function handle(Request $request)
            {
                return new Response('ok');
            }

            protected function dispatcherMiddleware(Request $request)
            {
                return [
                    new class() {
                        public function terminate(Request $request, Response $respone)
                        {
                            echo $request->getUrl();
                            echo $respone->getContent();
                        }
                    },
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
    public function itCanTerminate()
    {
        $karnel      = $this->app->make(Karnel::class);
        $response    = $karnel->handle(
            $request = new Request('/test')
        );

        $this->app->registerTerminate(static function () {
            echo 'terminated.';
        });

        ob_start();
        $karnel->terminate($request, $response);
        $out = ob_get_clean();

        $this->assertEquals('/testterminated.', $out);
    }
}
