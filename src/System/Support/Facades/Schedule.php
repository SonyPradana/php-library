<?php

declare(strict_types=1);

namespace System\Support\Facades;

/**
 * @method static ScheduleTime[]        getPools()
 * @method static ScheduleTime          call(mixed[] $params = [], \Closure $call_back)
 * @method static void                  execute()
 * @method static void                  setLogger(\System\Cron\InterpolateInterface $logger)
 * @method static void                  setTime(int $time)
 * @method static \System\Cron\Schedule add(\System\Cron\Schedule $schedule)
 * @method static void                  flush()
 *
 * @see System\Cron\Schedule
 */
final class Schedule extends Facade
{
    protected static function getAccessor()
    {
        return 'schedule';
    }
}
