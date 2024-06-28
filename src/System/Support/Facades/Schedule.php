<?php

declare(strict_types=1);

namespace System\Support\Facades;

/**
 * @method static \System\Cron\ScheduleTime[] getPools()
 * @method static \System\Cron\ScheduleTime   call(\Closure $call_back, $params = [])
 * @method static void                        execute()
 * @method static void                        setLogger()
 * @method static \System\Cron\Schedule       ref(\System\Cron\Schedule\Schedule $schedule)
 * @method static void                        flush()
 */
final class Schedule extends Facade
{
    protected static function getAccessor()
    {
        return 'schedule';
    }
}
