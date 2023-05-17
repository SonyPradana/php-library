<?php

namespace System\Cron;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class Schedule implements LoggerAwareInterface
{
    /** @var int|null */
    protected $time;

    /** @var ScheduleTime[] */
    protected array $pools = [];

    private ?LoggerInterface $logger;

    public function __construct(int $time, LoggerInterface $logger)
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

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
