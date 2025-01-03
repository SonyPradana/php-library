<?php

declare(strict_types=1);

namespace System\Time;

use System\Time\Exceptions\PropertyNotExist;
use System\Time\Exceptions\PropertyNotSetAble;
use System\Time\Traits\DateTimeFormatTrait;

/**
 * @property int    $timestamp
 * @property int    $year
 * @property int    $month
 * @property int    $day
 * @property int    $hour
 * @property int    $minute
 * @property int    $second
 * @property string $monthName
 * @property string $dayName
 * @property string $shortDay
 * @property string $timeZone
 * @property int    $age
 */
class Now
{
    use DateTimeFormatTrait;

    private \DateTime $date;

    /** @var int|false */
    private $timestamp;
    /** @var int */
    private $year;
    /** @var int */
    private $month;
    /** @var int */
    private $day;
    /** @var int */
    private $hour;
    /** @var int */
    private $minute;
    /** @var int */
    private $second;

    // other format date
    /** @var string */
    private $monthName;
    /** @var string */
    private $dayName;
    /** @var string */
    private $shortDay;
    /** @var string */
    private $timeZone;

    // other property
    private int $age;

    public function __construct(string $date_format = 'now', ?string $time_zone = null)
    {
        if (null !== $time_zone) {
            $time_zone = new \DateTimeZone($time_zone);
        }
        $this->date     = new \DateTime($date_format, $time_zone);

        $this->refresh();
    }

    public function __toString()
    {
        return implode('T', [
            $this->date->format('Y-m-d'),
            $this->date->format('H:i:s'),
        ]);
    }

    /**
     * Get private property.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        throw new PropertyNotExist($name);
    }

    /**
     * Set property by pase the `refresh` logic.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        if (method_exists($this, $name) && property_exists($this, $name)) {
            $this->{$name}($value);

            return;
        }

        throw new PropertyNotSetAble($name);
    }

    /**
     * Refresh property with current time.
     */
    private function refresh(): void
    {
        $this->timestamp = $this->date->getTimestamp();
        $this->year      = (int) $this->date->format('Y');
        $this->month     = (int) $this->date->format('n');
        $this->day       = (int) $this->date->format('d');
        $this->hour      = (int) $this->date->format('H');
        $this->minute    = (int) $this->date->format('i');
        $this->second    = (int) $this->date->format('s');

        $this->monthName = $this->date->format('F');
        $this->dayName   = $this->date->format('l');
        $this->timeZone  = $this->date->format('e');
        $this->shortDay  = $this->date->format('D');

        $this->age = max(0, (int) floor((time() - $this->timestamp) / (365.25 * 24 * 60 * 60)));
    }

    private function current(string $format, int $timestamp): string
    {
        $date = $this->date;

        return $date
            ->setTimestamp($timestamp)
            ->format($format)
        ;
    }

    /**
     * Get formated date time.
     */
    public function format(string $format): string
    {
        return $this->date->format($format);
    }

    /**
     * Set year time.
     *
     * @return self
     */
    public function year(int $year)
    {
        $this->date
        ->setDate(
            $year,
            $this->month,
            $this->day
        )
        ->setTime(
            $this->hour,
            $this->minute,
            $this->second
        );
        $this->refresh();

        return $this;
    }

    /**
     * set month time.
     *
     * @return self
     */
    public function month(int $month)
    {
        $this->date
        ->setDate(
            $this->year,
            $month,
            $this->day
        )
        ->setTime(
            $this->hour,
            $this->minute,
            $this->second
        );
        $this->refresh();

        return $this;
    }

    /**
     * Set day time.
     *
     * @return self
     */
    public function day(int $day)
    {
        $this->date
        ->setDate(
            $this->year,
            $this->month,
            $day
        )
        ->setTime(
            $this->hour,
            $this->minute,
            $this->second
        );
        $this->refresh();

        return $this;
    }

    /**
     * Set hour time.
     *
     * @return self
     */
    public function hour(int $hour)
    {
        $this->date
        ->setDate(
            $this->year,
            $this->month,
            $this->day
        )
        ->setTime(
            $hour,
            $this->minute,
            $this->second
        );
        $this->refresh();

        return $this;
    }

    /**
     * Set minute time.
     *
     * @return self
     */
    public function minute(int $minute)
    {
        $this->date
        ->setDate(
            $this->year,
            $this->month,
            $this->day
        )
        ->setTime(
            $this->hour,
            $minute,
            $this->second
        );
        $this->refresh();

        return $this;
    }

    /**
     * Set second time.
     *
     * @return self
     */
    public function second(int $second)
    {
        $this->date
        ->setDate(
            $this->year,
            $this->month,
            $this->day
        )
        ->setTime(
            $this->hour,
            $this->minute,
            $second
        );
        $this->refresh();

        return $this;
    }

    // month

    public function isJan(): bool
    {
        return $this->date->format('M') === 'Jan';
    }

    public function isFeb(): bool
    {
        return $this->date->format('M') === 'Feb';
    }

    public function isMar(): bool
    {
        return $this->date->format('M') === 'Mar';
    }

    public function isApr(): bool
    {
        return $this->date->format('M') === 'Apr';
    }

    public function isMay(): bool
    {
        return $this->date->format('M') === 'May';
    }

    public function isJun(): bool
    {
        return $this->date->format('M') === 'Jun';
    }

    public function isJul(): bool
    {
        return $this->date->format('M') === 'Jul';
    }

    public function isAug(): bool
    {
        return $this->date->format('M') === 'Aug';
    }

    public function isSep(): bool
    {
        return $this->date->format('M') === 'Sep';
    }

    public function isOct(): bool
    {
        return $this->date->format('M') === 'Oct';
    }

    public function isNov(): bool
    {
        return $this->date->format('M') === 'Nov';
    }

    //  day

    public function isDec(): bool
    {
        return $this->date->format('M') === 'Dec';
    }

    public function isMonday(): bool
    {
        return $this->date->format('D') === 'Mon';
    }

    public function isTuesday(): bool
    {
        return $this->date->format('D') === 'Tue';
    }

    public function isWednesday(): bool
    {
        return $this->date->format('D') === 'Wed';
    }

    public function isThursday(): bool
    {
        return $this->date->format('D') == 'Thu';
    }

    public function isFriday(): bool
    {
        return $this->date->format('D') == 'Fri';
    }

    public function isSaturday(): bool
    {
        return $this->date->format('D') == 'Sat';
    }

    public function isSunday(): bool
    {
        return $this->date->format('D') == 'Sun';
    }

    // next time

    public function isNextYear(): bool
    {
        $time = strtotime('next year');

        return $this->current('Y', $time) == $this->year;
    }

    public function isNextMonth(): bool
    {
        $time = strtotime('next month');

        return $this->current('n', $time) == $this->month;
    }

    public function isNextDay(): bool
    {
        $time = strtotime('next day');

        return $this->current('d', $time) == $this->day;
    }

    public function isNextHour(): bool
    {
        $time = strtotime('next hour');

        return $this->current('H', $time) == $this->hour;
    }

    public function isNextMinute(): bool
    {
        $time = strtotime('next minute');

        return $this->current('i', $time) == $this->minute;
    }

    // last time

    public function isLastYear(): bool
    {
        $time = strtotime('last year');

        return $this->current('Y', $time) == $this->year;
    }

    public function isLastMonth(): bool
    {
        $time = strtotime('last month');

        return $this->current('m', $time) == $this->month;
    }

    public function isLastDay(): bool
    {
        $time = strtotime('last day');

        return $this->current('d', $time) == $this->day;
    }

    public function isLastHour(): bool
    {
        $time = strtotime('last hour');

        return $this->current('H', $time) == $this->hour;
    }

    public function isLastMinute(): bool
    {
        $time = strtotime('last minute');

        return $this->current('i', $time) == $this->minute;
    }
}
