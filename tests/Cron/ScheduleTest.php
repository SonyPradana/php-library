<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\Cron\Schedule;
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
}
