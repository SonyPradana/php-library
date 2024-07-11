<?php

declare(strict_types=1);

namespace System\Test\Integrate\Commands;

use System\Cron\InterpolateInterface;
use System\Cron\Schedule;
use System\Integrate\Console\CronCommand;
use System\Support\Facades\Schedule as FacadesSchedule;

final class CronCommandsTest extends CommandTest
{
    private int $time;

    protected function setUp(): void
    {
        parent::setUp();
        $log = new class() implements InterpolateInterface {
            /**
             * @param array<string, mixed> $context
             */
            public function interpolate(string $message, array $context = []): void
            {
            }
        };
        $this->time = 10;
        $this->app->set('schedule', fn () => new Schedule($this->time, $log));
        new FacadesSchedule($this->app);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        FacadesSchedule::flush();
    }

    private function maker(string $argv): CronCommand
    {
        return new class($this->argv('cli cron')) extends CronCommand {
            public function __construct($argv)
            {
                parent::__construct($argv);
                $this->log = new class() implements InterpolateInterface {
                    /**
                     * @param array<string, mixed> $context
                     */
                    public function interpolate(string $message, array $context = []): void
                    {
                    }
                };
            }
        };
    }

    /**
     * @test
     */
    public function itCanCallCronCommandMain()
    {
        $cronCommand = $this->maker('cli cron');
        ob_start();
        $exit = $cronCommand->main();
        ob_get_clean();

        $this->assertSuccess($exit);
    }

    /**
     * @test
     */
    public function itCanCallCronCommandList()
    {
        $cronCommand = $this->maker('cli cron');
        ob_start();
        $exit = $cronCommand->list();
        ob_get_clean();

        $this->assertSuccess($exit);
    }

    /**
     * @test
     */
    public function itCanRegisterFromFacade()
    {
        FacadesSchedule::call(static fn (): int => 0)
            ->eventName('from-static')
            ->justInTime();

        $cronCommand = $this->maker('cli cron');
        ob_start();
        $exit = $cronCommand->list();
        $out  = ob_get_clean();

        $this->assertContain('from-static', $out);
        $this->assertContain('cli-schedule', $out);
        $this->assertSuccess($exit);
    }

    /**
     * @test
     */
    public function itCanRefreshTime()
    {
        FacadesSchedule::call(static fn (): int => 0)
            ->eventName('from-static')
            ->justInTime();

        $cronCommand = $this->maker('cli cron');

        $schedule = (fn () => $this->{'getSchedule'}())->call($cronCommand);
        $time     = (fn () => $this->{'time'})->call($schedule);

        $this->assertNotEquals($this->time, $time);
        $this->assertLessThanOrEqual(time(), $time, 'refresh time must >= now');
    }
}
