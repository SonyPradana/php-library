<?php

declare(strict_types=1);

namespace System\Test\Integrate\Console;

use PHPUnit\Framework\TestCase;
use System\Integrate\Application;
use System\Integrate\Console\Karnel;

final class KarnelTest extends TestCase
{
    private $app;
    private $karnel;

    protected function setUp(): void
    {
        $this->app = new Application('/');

        $this->app->set(
            System\Integrate\Console\Karnel::class,
            fn () => new $this->karnel($this->app)
        );

        $this->karnel = new class($this->app) extends Karnel {
            protected function commands(): array
            {
                return [
                    // new CommadMap([
                    // 'cmd'       => 'test',
                    // 'mode'      => 'full',
                    // 'class'     => '',
                    // 'fn'        => '',
                    // ])
                ];
            }

            public function test(): void
            {
                echo 'ok';
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
        $respone = $this->app->make(System\Integrate\Console\Karnel::class);
        ob_start();
        $exit    = $respone->handle(['cli', 'test']);
        $out     = ob_get_clean();

        $this->assertEquals(0, $exit);
        // $this->assertEquals(0, $out);
    }
}
