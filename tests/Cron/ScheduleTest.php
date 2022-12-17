<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\Cron\Schedule;
use System\Cron\ScheduleTime;
use System\Time\Now;

final class ScheduleTest extends TestCase
{
    /** @test */
    public function itCanContinueScheduleEventJobFail()
    {
        $time_trevel = new Now('09/07/2021 00:30:00');
        $Schedule    = new Schedule($time_trevel->timestamp);

        $Schedule
            ->call(function () {
                $devide = 0 / 0;
            })
            ->everyThirtyMinutes()
            ->eventName('test 30 minute');

        $Schedule
            ->call(function () {
                $this->assertTrue(true);
            })
            ->everyTenMinute()
            ->eventName('test 10 minute');

        $Schedule->execute();
    }

    /** @test */
    public function itCanRunRetrySchedule()
    {
        $time_trevel = new Now('09/07/2021 00:30:00');
        $Schedule    = new Schedule($time_trevel->timestamp);

        $Schedule
            ->call(function () {
                $devide = 0 / 0;
            })
            ->retry(5)
            ->everyThirtyMinutes()
            ->eventName('test 30 minute');

        $Schedule
            ->call(function () {
                $this->assertTrue(true);
            })
            ->everyTenMinute()
            ->eventName('test 10 minute');

        $Schedule->execute();
    }

    /** @test */
    public function itCanRunRetryCondtionSchedule()
    {
        $time_trevel = new Now('09/07/2021 00:30:00');
        $Schedule    = new Schedule($time_trevel->timestamp);

        $test = 1;

        $Schedule
            ->call(function () use (&$test) {
                $test++;
            })
            ->retryIf(true)
            ->everyThirtyMinutes()
            ->eventName('test 30 minute');

        $Schedule->execute();
        $this->assertEquals(3, $test);
    }

    /** @test */
    public function itCanLogCronExectWhateverCondition()
    {
        $time_trevel = new Now('09/07/2021 00:30:00');
        $Schedule    = new class($time_trevel->timestamp) extends Schedule {
            public function call(Closure $call_back, $params = [])
            {
                $cron = new class($call_back, $params, $this->time) extends ScheduleTime {
                    protected function interpolate($message, array $contex): void
                    {
                        echo 'works';
                    }
                };

                return $this->pools[] = new $cron($call_back, $params, $this->time);
            }
        };

        $Schedule
            ->call(fn () => 0 / 0)
            ->retry(20)
            ->everyThirtyMinutes()
            ->eventName('test 30 minute');

        ob_start();
        $Schedule->execute();
        $out = ob_get_clean();

        $this->assertEquals(str_repeat('works', 20), $out);
    }
}
