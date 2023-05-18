<?php

use PHPUnit\Framework\TestCase;
use System\Cron\InterpolateInterface;
use System\Cron\Schedule;
use System\Cron\ScheduleTime;

final class BasicCronTest extends TestCase
{
    private ?InterpolateInterface $logger;

    protected function setUp(): void
    {
        $this->logger = new class() implements InterpolateInterface {
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

    private function sampleSchedules(): Schedule
    {
        $schedule = new Schedule(now()->timestamp, $this->logger);
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
        $animusly = new Schedule(now()->timestamp, $this->logger);
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
