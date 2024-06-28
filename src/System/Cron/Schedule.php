<?php

declare(strict_types=1);

namespace System\Cron;

class Schedule
{
    /** @var int|null */
    protected $time;

    /** @var ScheduleTime[] */
    protected array $pools = [];

    private ?InterpolateInterface $logger;

    public function __construct(int $time, InterpolateInterface $logger)
    {
        $this->time   = $time;
        $this->logger = $logger;
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
            $cron->setLogger($this->logger);
            do {
                $cron->exect();
            } while ($cron->retryAtempts() > 0);

            if ($cron->isRetry()) {
                $cron->exect();
            }
        }
    }

    public function setLogger(InterpolateInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Add Schedule pool to the collection pool.
     */
    public function add(Schedule $schedule): self
    {
        foreach ($schedule->getPools() as $time) {
            $this->pools[] = $time;
        }

        return $this;
    }

    /**
     * Clear schedule pool.
     */
    public function flush(): void
    {
        $this->pools = [];
    }
}
