<?php

namespace System\Cron;

class Schedule
{
    /** @var int|null */
    protected $time;

    /** @var ScheduleTime[] */
    protected array $pools = [];

    public function __construct(int $time = null)
    {
        $this->time = $time ?? time();
    }

    /**
     * @return ScheduleTime[]
     */
    public function getPools()
    {
        return $this->pools;
    }

    /**
     * @param mixed[] $params
     *
     * @return ScheduleTime
     */
    public function call(\Closure $call_back, $params = [])
    {
        return $this->pools[] = new ScheduleTime($call_back, $params, $this->time);
    }

    public function execute(): void
    {
        foreach ($this->pools as $cron) {
            do {
                $cron->exect();
            } while ($cron->retryAtempts() > 0);
        }
    }
}
