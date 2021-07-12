<?php

namespace System\Time;

class Now
{
  private $time;
  // public
  public $timestamp;
  public $year;
  public $month;
  public $day;
  public $hour;
  public $minute;
  public $second;
  // other format date
  public $monthName;
  public $dayName;
  public $timeZone;
  // other property
  public $age;

  public function __construct(string $date_format = "now")
  {
    $this->time = strtotime($date_format);

    // set porperty
    $this->refresh();
  }

  public function __toString()
  {
    return implode("T", array(
      date("Y-m-d", $this->time),
      date("H:i:s", $this->time)
    ));
  }

  private function refresh()
  {
    $this->timestamp = $this->time;
    $this->year   = date("Y", $this->time);
    $this->month  = date("n", $this->time);
    $this->day    = date("d", $this->time);
    $this->hour   = date("H", $this->time);
    $this->minute = date("i", $this->time);
    $this->second = date("s", $this->time);

    $this->monthName = date("F", $this->time);
    $this->dayName = date("l", $this->time);
    $this->timeZone = date("e", $this->time);
    //
    $age = time() - $this->time;
    $this->age = abs(floor($age / (365*60*60*24)));
  }

  public function year(int $year)
  {
    $this->time = mktime(
      $this->hour,
      $this->minute,
      $this->second,
      $this->month,
      $this->day,
      $year
    );
    $this->refresh();
    return $this;
  }

  public function month(int $month)
  {
    $this->time = mktime(
      $this->hour,
      $this->minute,
      $this->second,
      $month,
      $this->day,
      $this->year
    );
    $this->refresh();
    return $this;
  }

  public function day(int $day)
  {
    $this->time = mktime(
      $this->hour,
      $this->minute,
      $this->second,
      $this->month,
      $day,
      $this->year
    );
    $this->refresh();
    return $this;
  }

  public function hour(int $hour)
  {
    $this->time = mktime(
      $hour,
      $this->minute,
      $this->second,
      $this->month,
      $this->day,
      $this->year
    );
    $this->refresh();
    return $this;
  }

  public function minute(int $minute)
  {
    $this->time = mktime(
      $this->hour,
      $minute,
      $this->second,
      $this->month,
      $this->day,
      $this->year
    );
    $this->refresh();
    return $this;
  }

  public function second(int $second)
  {
    $this->time = mktime(
      $this->hour,
      $this->minute,
      $second,
      $this->month,
      $this->day,
      $this->year
    );
    $this->refresh();
    return $this;
  }

  // month

  public function isJan(): bool
  {
    return date("M", $this->time) == "Jan";
  }

  public function isFeb(): bool
  {
    return date("M", $this->time) == "Feb";
  }

  public function isMar(): bool
  {
    return date("M", $this->time) == "Mar";
  }

  public function isApr(): bool
  {
    return date("M", $this->time) == "Apr";
  }

  public function isMay(): bool
  {
    return date("M", $this->time) == "May";
  }

  public function isJun(): bool
  {
    return date("M", $this->time) == "Jun";
  }

  public function isJul(): bool
  {
    return date("M", $this->time) == "Jul";
  }

  public function isAug(): bool
  {
    return date("M", $this->time) == "Aug";
  }

  public function isSep(): bool
  {
    return date("M", $this->time) == "Sep";
  }

  public function isOct(): bool
  {
    return date("M", $this->time) == "Oct";
  }

  public function isNov(): bool
  {
    return date("M", $this->time) == "Nov";
  }

  //  day

  public function isDec(): bool
  {
    return date("M", $this->time) == "Dec";
  }

  public function isMonday(): bool
  {
    return date("D", $this->time) == "Mon";
  }

  public function isTuesday(): bool
  {
    return date("D", $this->time) == "Tue";
  }

  public function isWednesday(): bool
  {
    return date("D", $this->time) == "Wed";
  }

  public function isThursday(): bool
  {
    return date("D", $this->time) == "Thu";
  }

  public function isFriday(): bool
  {
    return date("D", $this->time) == "Fri";
  }

  public function isSaturday(): bool
  {
    return date("D", $this->time) == "Sat";
  }

  public function isSunday(): bool
  {
    return date("D", $this->time) == "Sun";
  }

  // next time

  public function isNextYear(): bool
  {
    return date("Y") + 1 == $this->year;
  }

  public function isNextMonth(): bool
  {
    $time = strtotime("next month");
    return date("n", $time) == $this->month;
  }

  public function isNextDay(): bool
  {
    $time = strtotime("next day");
    return date("m", $time) == $this->day;
  }

  public function isNextHour(): bool
  {
    $time = strtotime("next hour");
    return date("H", $time) == $this->hour;
  }

  public function isNextMinute(): bool
  {
    $time = strtotime("next minute");
    return date("i", $time) == $this->minute;
  }

  // last time

  public function isLastYear(): bool
  {
    return date("Y") - 1 == $this->year;
  }

  public function isLastMonth(): bool
  {
    $time = strtotime("next month");
    return date("m", $time) == $this->month;
  }

  public function isLastDay(): bool
  {
    $time = strtotime("next day");
    return date("m", $time) == $this->day;
  }

  public function isLastHour(): bool
  {
    $time = strtotime("next hour");
    return date("H", $time) == $this->hour;
  }

  public function isLastMinute(): bool
  {
    $time = strtotime("next minute");
    return date("i", $time) == $this->minute;
  }
}
