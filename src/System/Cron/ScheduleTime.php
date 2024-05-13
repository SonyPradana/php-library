<?php

declare(strict_types=1);

namespace System\Cron;

use Closure;

class ScheduleTime
{
    /**
     * Closure to call if due the time.
     *
     * @var \Closure
     */
    private $call_back;

    /**
     * Parameter of closure.
     *
     * @var mixed[]
     */
    private $params = [];

    /**
     * Current time.
     */
    private int $time;

    /**
     * Event name.
     */
    private string $event_name = 'animus';

    /**
     * Times to check (cron time).
     *
     * @var array<int, array<string, int|string>|int>
     */
    private $time_exect;

    /**
     * Cron time name.
     */
    private string $time_name = '';

    /**
     * Check is animus cron job.
     */
    private bool $animusly  = false;

    /**
     * Determinate cron execute run error.
     */
    private bool $is_fail = false;

    /**
     * Determinate retry maxsimum execute.
     */
    private int $retry_atempts = 0;

    /**
     * Reatry if condition is true.
     */
    private bool $retry_condition = false;

    /**
     * Skip task (due) in some condition.
     */
    private bool $skip = false;

    private ?InterpolateInterface $logger = null;

    /**
     * Contructor.
     *
     * @param mixed[] $params
     */
    public function __construct(\Closure $call_back, array $params, int $timestamp)
    {
        $this->call_back  = $call_back;
        $this->params     = $params;
        $this->time       = $timestamp;
        $this->time_exect = [
            [
                'D' => date('D', $this->time),
                'd' => date('d', $this->time),
                'h' => date('H', $this->time),
                'm' => date('i', $this->time),
            ],
        ];
    }

    public function eventName(string $val): self
    {
        $this->event_name = $val;

        return $this;
    }

    public function animusly(bool $run_as_animusly = true): self
    {
        $this->animusly = $run_as_animusly;

        return $this;
    }

    public function isAnimusly(): bool
    {
        return $this->animusly;
    }

    public function getEventname(): string
    {
        return $this->event_name;
    }

    public function getTimeName(): string
    {
        return $this->time_name;
    }

    /**
     * Get cron time.
     *
     * @return  array<int, array<string, int|string>|int> */
    public function getTimeExect()
    {
        return $this->time_exect;
    }

    public function isFail(): bool
    {
        return $this->is_fail;
    }

    public function retryAtempts(): int
    {
        return $this->retry_atempts;
    }

    public function retry(int $atempt): self
    {
        $this->retry_atempts = $atempt;

        return $this;
    }

    public function retryIf(bool $condition): self
    {
        $this->retry_condition = $condition;

        return $this;
    }

    public function isRetry(): bool
    {
        return $this->retry_condition;
    }

    /**
     * Skip schedule in due time.
     *
     * @param bool|\Closure(): bool $skip_when True if skip the due schedule
     */
    public function skip($skip_when): self
    {
        if ($skip_when instanceof \Closure) {
            $skip_when = $skip_when();
        }

        $this->skip = $skip_when;

        return $this;
    }

    // TODO: get next due time

    public function exect(): void
    {
        if ($this->isDue() && false === $this->skip) {
            // stopwatch
            $watch_start = microtime(true);

            try {
                $out_put             = call_user_func($this->call_back, $this->params) ?? [];
                $this->retry_atempts = 0;
                $this->is_fail       = false;
            } catch (\Throwable $th) {
                $this->retry_atempts--;
                $this->is_fail = true;
                $out_put       = [
                    'error' => $th->getMessage(),
                ];
            }

            // stopwatch
            $watch_end = round(microtime(true) - $watch_start, 3) * 1000;

            // send command log
            if (!$this->animusly) {
                if (null !== $this->logger) {
                    $this->logger->interpolate(
                        $this->event_name,
                        [
                            'excute_time'   => $watch_end,
                            'cron_time'     => $this->time,
                            'event_name'    => $this->event_name,
                            'atempts'       => $this->retry_atempts,
                            'error_message' => $out_put,
                        ]
                    );
                }
            }
        }
    }

    /**
     * @param array<string, mixed> $contex
     */
    protected function interpolate(string $message, array $contex): void
    {
        // do stuff
    }

    public function setLogger(InterpolateInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function isDue(): bool
    {
        $events = $this->time_exect;

        $dayLetter  = date('D', $this->time);
        $day        = date('d', $this->time);
        $hour       = date('H', $this->time);
        $minute     = date('i', $this->time);

        foreach ($events as $event) {
            $eventDayLetter = $event['D'] ?? $dayLetter; // default day letter every event

            if ($eventDayLetter == $dayLetter
            && $event['d'] == $day
            && $event['h'] == $hour
            && $event['m'] == $minute) {
                return true;
            }
        }

        return false;
    }

    public function justInTime(): self
    {
        $this->time_name  = __FUNCTION__;
        $this->time_exect = [
            [
                'D' => date('D', $this->time),
                'd' => date('d', $this->time),
                'h' => date('H', $this->time),
                'm' => date('i', $this->time),
            ],
        ];

        return $this;
    }

    public function everyTenMinute(): self
    {
        $this->time_name = __FUNCTION__;
        $minute          = [];
        foreach (range(0, 59) as $time) {
            if ($time % 10 == 0) {
                $minute[] = [
                    'd' => date('d', $this->time),
                    'h' => date('H', $this->time),
                    'm' => $time,
                ];
            }
        }

        $this->time_exect = $minute;

        return $this;
    }

    public function everyThirtyMinutes(): self
    {
        $this->time_name  = __FUNCTION__;
        $this->time_exect = [
            [
                'd' => date('d', $this->time),
                'h' => date('H', $this->time),
                'm' => 0,
            ],
            [
                'd' => date('d', $this->time),
                'h' => date('H', $this->time),
                'm' => 30,
            ],
        ];

        return $this;
    }

    public function everyTwoHour(): self
    {
        $this->time_name = __FUNCTION__;

        $thisDay = date('d', $this->time);
        $hourly  = []; // from 00.00 to 23.00 (12 time)
        foreach (range(0, 23) as $time) {
            if ($time % 2 == 0) {
                $hourly[] = [
                    'd' => $thisDay,
                    'h' => $time,
                    'm' => 0,
                ];
            }
        }

        $this->time_exect = $hourly;

        return $this;
    }

    public function everyTwelveHour(): self
    {
        $this->time_name  = __FUNCTION__;
        $this->time_exect = [
            [
                'd' => date('d', $this->time),
                'h' => 0,
                'm' => 0,
            ],
            [
                'd' => date('d', $this->time),
                'h' => 12,
                'm' => 0,
            ],
        ];

        return $this;
    }

    public function hourly(): self
    {
        $this->time_name = __FUNCTION__;
        $hourly          = []; // from 00.00 to 23.00 (24 time)
        foreach (range(0, 23) as $time) {
            $hourly[] = [
                'd' => date('d', $this->time),
                'h' => $time,
                'm' => 0,
            ];
        }

        $this->time_exect = $hourly;

        return $this;
    }

    public function hourlyAt(int $hour24): self
    {
        $this->time_name  = __FUNCTION__;
        $this->time_exect = [
            [
                'd' => date('d', $this->time),
                'h' => $hour24,
                'm' => 0,
            ],
        ];

        return $this;
    }

    public function daily(): self
    {
        $this->time_name  = __FUNCTION__;
        $this->time_exect = [
            // from day 1 to 31 (31 time)
            ['d' => (int) date('d'), 'h' => 0, 'm' => 0],
        ];

        return $this;
    }

    public function dailyAt(int $day): self
    {
        $this->time_name  = __FUNCTION__;
        $this->time_exect = [
            [
                'd' => $day,
                'h' => 0,
                'm' => 0,
            ],
        ];

        return $this;
    }

    public function weekly(): self
    {
        $this->time_name  = __FUNCTION__;
        $this->time_exect = [
            [
                'D' => 'Sun',
                'd' => date('d', $this->time),
                'h' => 0,
                'm' => 0,
            ],
        ];

        return $this;
    }

    public function mountly(): self
    {
        $this->time_name  = __FUNCTION__;
        $this->time_exect = [
            [
                'd' => 1,
                'h' => 0,
                'm' => 0,
            ],
        ];

        return $this;
    }
}
