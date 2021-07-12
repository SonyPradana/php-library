<?php

namespace System\Cron;

class Schedule
{
  private $time;
  private $pools = [];

  public function __construct(int $time = null)
  {
    $this->time = $time ?? time();
  }

  public function getPools()
  {
    return $this->pools;
  }

  public function call($call_back, $params = [])
  {
    return $this->pools[] = new ScheduleTime($call_back, $params, $this->time);
  }

  public function execute()
  {
    foreach ($this->pools as $cron) {
      $cron->exect();
    }
  }
}
