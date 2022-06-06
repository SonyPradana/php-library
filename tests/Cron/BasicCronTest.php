<?php

use PHPUnit\Framework\TestCase;
use System\Cron\Schedule;
use System\Cron\ScheduleTime;

final class BasicCronTest extends TestCase
{
    private function sampleSchedules(): Schedule
    {
        $schedule = new Schedule();
        $schedule
      ->call(fn (): string => 'test')
      ->justInTime();

        return $schedule;
    }

    /**
     * @test
     */
    public function itCorrectScheduleClass(): void
    {
        foreach ($this->sampleSchedules()->getPools() as $scheduleItem) {
            $this->assertInstanceOf(ScheduleTime::class, $scheduleItem, 'this is scheduletime');
        }
    }

    /**
     * @test
     */
    public function itScheduleRunAnymusly(): void
    {
        $animusly = new Schedule();
        $animusly
      ->call(fn (): string => 'is run animusly')
      ->justInTime()
      ->eventName('test 01')
      ->animusly();

        $animusly
      ->call(fn (): string => 'is run animusly')
      ->hourly()
      ->eventName('test 02')
      ->animusly();

        foreach ($animusly->getPools() as $scheduleItem) {
            if ($scheduleItem instanceof ScheduleTime) {
                $this->assertTrue($scheduleItem->isAnimusly());
            }
        }
    }
}
