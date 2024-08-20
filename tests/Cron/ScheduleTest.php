<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\Cron\InterpolateInterface;
use System\Cron\Schedule;
use System\Time\Now;

final class ScheduleTest extends TestCase
{
    private ?InterpolateInterface $logger;

    protected function setUp(): void
    {
        $this->logger = new class implements InterpolateInterface {
            public function interpolate(string $message, array $context = []): void
            {
                echo 'works';
            }
        };
    }

    protected function tearDown(): void
    {
        $this->logger = null;
    }

    /** @test */
    public function itCanContinueScheduleEventJobFail()
    {
        $time_trevel = new Now('09/07/2021 00:30:00');
        $schedule    = new Schedule($time_trevel->timestamp, $this->logger);

        $schedule
            ->call(function () {
                $devide = 0 / 0;
            })
            ->everyThirtyMinutes()
            ->eventName('test 30 minute');

        $schedule
            ->call(function () {
                $this->assertTrue(true);
            })
            ->everyTenMinute()
            ->eventName('test 10 minute');

        ob_start();
        $schedule->execute();
        ob_get_clean();
    }

    /** @test */
    public function itCanRunRetrySchedule()
    {
        $time_trevel = new Now('09/07/2021 00:30:00');
        $schedule    = new Schedule($time_trevel->timestamp, $this->logger);

        $schedule
            ->call(function () {
                $devide = 0 / 0;
            })
            ->retry(5)
            ->everyThirtyMinutes()
            ->eventName('test 30 minute');

        $schedule
            ->call(function () {
                $this->assertTrue(true);
            })
            ->everyTenMinute()
            ->eventName('test 10 minute');

        ob_start();
        $schedule->execute();
        ob_get_clean();
    }

    /** @test */
    public function itCanRunRetryCondtionSchedule()
    {
        $time_trevel = new Now('09/07/2021 00:30:00');
        $schedule    = new Schedule($time_trevel->timestamp, $this->logger);

        $test = 1;

        $schedule
            ->call(function () use (&$test) {
                $test++;
            })
            ->retryIf(true)
            ->everyThirtyMinutes()
            ->eventName('test 30 minute');

        ob_start();
        $schedule->execute();
        ob_get_clean();
        $this->assertEquals(3, $test);
    }

    /** @test */
    public function itCanLogCronExectWhateverCondition()
    {
        $time_trevel = new Now('09/07/2021 00:30:00');
        $schedule    = new Schedule($time_trevel->timestamp, $this->logger);

        $schedule
            ->call(fn () => 0 / 0)
            ->retry(20)
            ->everyThirtyMinutes()
            ->eventName('test 30 minute');

        ob_start();
        $schedule->execute();
        $out = ob_get_clean();

        $this->assertEquals(str_repeat('works', 20), $out);
    }

    /** @test */
    public function itCanSkipScheduleEventIsDue()
    {
        $time_trevel = new Now('09/07/2021 00:30:00');
        $schedule    = new Schedule($time_trevel->timestamp, $this->logger);
        $always_false= false;

        $schedule
            ->call(function () use (&$always_false) {
                $always_false = true;

                return 'never call';
            })
            ->justInTime()
            ->skip(fn (): bool => true);

        $schedule->execute();
        $this->assertFalse($always_false);
    }
}
