<?php

declare(strict_types=1);

namespace System\Support\Facades;

/**
 * @method static \System\Cron\ScheduleTime[] getPools()
 * @method static \System\Cron\ScheduleTime   call(\Closure $call_back, array $params = [])
 * @method static void                        execute()
 * @method static void                        setLogger(\System\Cron\Schedule\InterpolateInterface $logger)
 * @method static void                        setTime(int $time)
 * @method static \System\Cron\Schedule       add(\System\Cron\Schedule $schedule)
 * @method static void                        flush()
 */
final class Schedule extends Facade
{
    protected static function getAccessor()
    {
        return 'schedule';
    }
}
