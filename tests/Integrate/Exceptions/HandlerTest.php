<?php

declare(strict_types=1);

namespace System\Test\Integrate\Exceptions;

use PHPUnit\Framework\TestCase;
use System\Http\Request;
use System\Http\Response;
use System\Integrate\Application;
use System\Integrate\Exceptions\Handler;
use System\Integrate\Http\Exception\HttpException;
use System\Integrate\Http\Karnel;

final class HandlerTest extends TestCase
{
    private Application $app;
    private Karnel $karnel;
    private Handler $handler;
    /**
     * Mock Logger.
     *
     * @var string[]
     */
    public static array $logs = [];

    protected function setUp(): void
    {
        $this->app = new Application(__DIR__);

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

        $this->handler = new class() extends Handler {
            public function render(Request $request, \Throwable $th): Response
            {
                if ($th instanceof HttpException) {
                    return new Response($th->getMessage(), $th->getStatusCode(), $th->getHeaders());
                }

                return parent::render($request, $th);
            }

            public function report(\Throwable $th): void
            {
                HandlerTest::$logs[] = $th->getMessage();
            }
        };
    }

    protected function tearDown(): void
    {
        $this->app->flush();
        HandlerTest::$logs = [];
    }

    /** @test */
    public function itCanRenderException()
    {
        $karnel      = $this->app->make(Karnel::class);
        $response    = $karnel->handle(new Request('/test'));

        $this->assertEquals('Test Exception', $response->getContent());
        $this->assertEquals(500, $response->getStatusCode());
    }

    /** @test */
    public function itCanReportException()
    {
        $karnel      = $this->app->make(Karnel::class);
        $karnel->handle(new Request('/test'));

        $this->assertEquals(['Test Exception'], HandlerTest::$logs);
    }
}
