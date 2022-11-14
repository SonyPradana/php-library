<?php

declare(strict_types=1);

namespace System\Cron;

class ScheduleTime
{
    /**
     * Closure to call if due the time.
     *
     * @var callable
     */
    private $call_back;

    /**
     * Parameter of closure.
     *
     * @var array
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
     * Contructor.
     */
    public function __construct(callable $call_back, array $params, int $timestamp)
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

    public function eventName(string $val)
    {
        $this->event_name = $val;

        return $this;
    }

    public function animusly(bool $run_as_animusly = true)
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

    // TODO: get next due time

    public function exect(): void
    {
        if ($this->isDue()) {
            // stopwatch
            $watch_start = microtime(true);

            $out_put = call_user_func($this->call_back, $this->params) ?? [];

            // stopwatch
            $watch_end = round(microtime(true) - $watch_start, 3) * 1000;

            // send command log
            if (!$this->animusly) {
                $this->interpolate($out_put, [
                    'excute_time' => $watch_end,
                    'cron_time'   => $this->time,
                    'event_name'  => $this->event_name,
                ]);
            }
        }
    }

    protected function interpolate(mixed $message, array $contex)
    {
        // do stuff
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
