<?php

use PHPUnit\Framework\TestCase;
use System\Cron\InterpolateInterface;
use System\Cron\Schedule;
use System\Cron\ScheduleTime;
use System\Time\Now;

final class CronTimeTest extends TestCase
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

    /**
     * @test
     */
    public function itRunOnlyJustInTime(): void
    {
        $animusly = new Schedule(now()->timestamp, $this->logger);
        $animusly
            ->call(fn (): string => 'due time')
            ->justInTime()
            ->eventName('test 01');

        foreach ($animusly->getPools() as $scheduleItem) {
            if ($scheduleItem instanceof ScheduleTime) {
                $this->assertTrue($scheduleItem->isDue());
            }
        }
    }

    /**
     * @test
     */
    public function itRunOnlyEveryTenMinute(): void
    {
        $time_trevel = new Now('09/07/2021 00:00:00');
        $animusly    = new Schedule($time_trevel->timestamp, $this->logger);
        $animusly
            ->call(fn (): string => 'due time')
            ->everyTenMinute()
            ->eventName('test 10 minute');

        foreach ($animusly->getPools() as $scheduleItem) {
            if ($scheduleItem instanceof ScheduleTime) {
                $this->assertTrue($scheduleItem->isDue());
            }
        }
    }

    /**
     * @test
     */
    public function itRunOnlyEveryThirtyMinutes(): void
    {
        $time_trevel = new Now('09/07/2021 00:30:00');
        $animusly    = new Schedule($time_trevel->timestamp, $this->logger);
        $animusly
            ->call(fn (): string => 'due time')
            ->everyThirtyMinutes()
            ->eventName('test 30 minute');

        foreach ($animusly->getPools() as $scheduleItem) {
            if ($scheduleItem instanceof ScheduleTime) {
                $this->assertTrue($scheduleItem->isDue());
            }
        }
    }

    /**
     * @test
     */
    public function itRunOnlyEveryTwoHour(): void
    {
        $time_trevel = new Now('09/07/2021 02:00:00');
        $animusly    = new Schedule($time_trevel->timestamp, $this->logger);
        $animusly
            ->call(fn (): string => 'due time')
            ->everyTwoHour()
            ->eventName('test 2 hour');

        foreach ($animusly->getPools() as $scheduleItem) {
            if ($scheduleItem instanceof ScheduleTime) {
                $this->assertTrue($scheduleItem->isDue());
            }
        }
    }

    /**
     * @test
     */
    public function itRunOnlyEveryTwelveHour(): void
    {
        $time_trevel = new Now('09/07/2021 12:00:00');
        $animusly    = new Schedule($time_trevel->timestamp, $this->logger);
        $animusly
            ->call(fn (): string => 'due time')
            ->everyTwelveHour()
            ->eventName('test 12 hour');

        foreach ($animusly->getPools() as $scheduleItem) {
            if ($scheduleItem instanceof ScheduleTime) {
                $this->assertTrue($scheduleItem->isDue());
            }
        }
    }

    /**
     * @test
     */
    public function itRunOnlyHourly(): void
    {
        $time_trevel = new Now('09/07/2021 00:00:00');
        $animusly    = new Schedule($time_trevel->timestamp, $this->logger);
        $animusly
            ->call(fn (): string => 'due time')
            ->hourly()
            ->eventName('test hourly');

        foreach ($animusly->getPools() as $scheduleItem) {
            if ($scheduleItem instanceof ScheduleTime) {
                $this->assertTrue($scheduleItem->isDue());
            }
        }
    }

    /**
     * @test
     */
    public function itRunOnlyHourlyAt(): void
    {
        $time_trevel = new Now('09/07/2021 05:00:00');
        $animusly    = new Schedule($time_trevel->timestamp, $this->logger);
        $animusly
            ->call(fn (): string => 'due time')
            ->hourlyAt(5)
            ->eventName('test hourlyAt 5 hour');

        foreach ($animusly->getPools() as $scheduleItem) {
            if ($scheduleItem instanceof ScheduleTime) {
                $this->assertTrue($scheduleItem->isDue());
            }
        }
    }

    /**
     * @test
     */
    public function itRunOnlyDaily(): void
    {
        $time_trevel = new Now('00:00:00');
        $animusly    = new Schedule($time_trevel->timestamp, $this->logger);
        $animusly
            ->call(fn (): string => 'due time')
            ->daily()
            ->eventName('test daily');

        foreach ($animusly->getPools() as $scheduleItem) {
            if ($scheduleItem instanceof ScheduleTime) {
                // die(var_dump($scheduleItem->getTimeExect()));
                $this->assertTrue($scheduleItem->isDue());
            }
        }
    }

    /**
     * @test
     */
    public function itRunOnlyDailyAt(): void
    {
        $time_trevel = new Now('12/12/2012 00:00:00');
        $animusly    = new Schedule($time_trevel->timestamp, $this->logger);
        $animusly
            ->call(fn (): string => 'due time')
            ->dailyAt(12)
            ->eventName('test dailyAt 12');

        foreach ($animusly->getPools() as $scheduleItem) {
            if ($scheduleItem instanceof ScheduleTime) {
                $this->assertTrue($scheduleItem->isDue());
            }
        }
    }

    /**
     * @test
     */
    public function itRunOnlyWeekly(): void
    {
        $time_trevel = new Now('12/16/2012 00:00:00');
        $animusly    = new Schedule($time_trevel->timestamp, $this->logger);
        $animusly
            ->call(fn (): string => 'due time')
            ->weekly()
            ->eventName('test weekly');

        foreach ($animusly->getPools() as $scheduleItem) {
            if ($scheduleItem instanceof ScheduleTime) {
                // die(var_dump($scheduleItem->getTimeExect()));
                $this->assertTrue($scheduleItem->isDue());
            }
        }
    }

    /**
     * @test
     */
    public function itRunOnlyMountly(): void
    {
        $time_trevel = new Now('1/1/2012 00:00:00');
        $animusly    = new Schedule($time_trevel->timestamp, $this->logger);
        $animusly
            ->call(fn (): string => 'due time')
            ->mountly()
            ->eventName('test mountly');

        foreach ($animusly->getPools() as $scheduleItem) {
            if ($scheduleItem instanceof ScheduleTime) {
                $this->assertTrue($scheduleItem->isDue());
            }
        }
    }
}
