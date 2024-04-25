<?php

declare(strict_types=1);

namespace System\Test\Integrate\Console;

use PHPUnit\Framework\TestCase;
use System\Console\Command;
use System\Integrate\Application;
use System\Integrate\Console\Karnel;
use System\Integrate\Exceptions\Handler;
use System\Integrate\ValueObjects\CommandMap;

final class KarnelHandleExceptionTest extends TestCase
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
        $this->app = new Application('/');

        $this->app->set(
            Karnel::class,
            fn () => new $this->karnel($this->app)
        );

        $this->app->set(
            Handler::class,
            fn () => $this->handler
        );

        $this->karnel = new class($this->app) extends Karnel {
            protected function commands()
            {
                return [
                    new CommandMap([
                        'pattern'   => 'karnel:test',
                        'fn'        => [ConsoleKarnelTest::class, 'main'],
                    ]),
                ];
            }
        };

        $this->handler = new class() extends Handler {
            public function report(\Throwable $th): void
            {
                KarnelHandleExceptionTest::$logs[] = $th->getMessage();
            }
        };
    }

    protected function tearDown(): void
    {
        $this->app->flush();
    }

    /** @test */
    public function itCanReportException()
    {
        $karnel      = $this->app->make(Karnel::class);
        $exit        = $karnel->handle(['cli', 'karnel:test']);

        $this->assertEquals(['just test'], KarnelHandleExceptionTest::$logs);
    }
}

class ConsoleKarnelTest extends Command
{
    /**
     * Register command.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
        [
            'pattern' => 'cron',
            'fn'      => [self::class, 'main'],
        ],
    ];

    public function main(): int
    {
        throw new \Exception('just test');
    }
}
