<?php

declare(strict_types=1);

namespace System\Cron;

class Schedule
{
    /** @var ScheduleTime[] */
    protected array $pools = [];

    public function __construct(
        protected ?int $time = null,
        private ?InterpolateInterface $logger = null)
    {
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

    public function setTime(int $time): void
    {
        $this->time = $time;
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
