<?php

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use System\Cron\Schedule;
use System\Cron\ScheduleTime;

final class BasicCronTest extends TestCase
{
    private ?LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->logger = new class() implements LoggerInterface {
            public function emergency(string|Stringable $message, array $context = []): void
            {
            }

            public function critical(string|Stringable $message, array $context = []): void
            {
            }

            public function error(string|Stringable $message, array $context = []): void
            {
            }

            public function warning(string|Stringable $message, array $context = []): void
            {
            }

            public function notice(string|Stringable $message, array $context = []): void
            {
            }

            public function debug(string|Stringable $message, array $context = []): void
            {
            }

            public function alert(string|Stringable $message, array $context = []): void
            {
            }

            public function info(string|Stringable $message, array $context = []): void
            {
                echo 'works';
            }

            public function log($level, string|Stringable $message, array $context = []): void
            {
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
