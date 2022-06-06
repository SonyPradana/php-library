<?php

use PHPUnit\Framework\TestCase;
use System\Time\Now;

final class TimeTravelTest extends TestCase
{
    /**
     * @test
     */
    public function itSameWithCurrentTime()
    {
        $now = new Now();

        $this->assertEquals(
      time(),
      $now->timestamp,
      'timestamp must equal'
    );

        $this->assertEquals(
      date('Y'),
      $now->year,
      'timestamp must equal'
    );

        $this->assertEquals(
      date('n'),
      $now->month,
      'the time must same with this day'
    );

        $this->assertEquals(
      date('d'),
      $now->day,
      'the time must same with this day'
    );

        $this->assertEquals(
      date('H'),
      $now->hour,
      'the time must same with this hour'
    );

        $this->assertEquals(
      date('i'),
      $now->minute,
      'the time must same with this minute'
    );

        $this->assertEquals(
      date('s'),
      $now->second,
      'the time must same with this second'
    );

        $this->assertEquals(
      date('l'),
      $now->dayName,
      'the time must same with this day name'
    );

        $this->assertEquals(
      date('F'),
      $now->monthName,
      'the time must same with this day name'
    );
    }

    /**
     * @test
     */
    public function itSameWithCustumeTime(): void
    {
        $now = new Now();
        date_default_timezone_set('Asia/Jakarta');
        $time = 1625316759; // 7/3/2021, 19:52:39 PM
        // costume time
        $now->year(2021);
        $now->month(7);
        $now->day(3);
        $now->hour(19);
        $now->minute(52);
        $now->second(39);

        $this->assertEquals(
      date('Y', $time),
      $now->year,
      'timestamp must equal'
    );

        $this->assertEquals(
      date('n', $time),
      $now->month,
      'the time must same with this day'
    );

        $this->assertEquals(
      date('d', $time),
      $now->day,
      'the time must same with this day'
    );

        $this->assertEquals(
      date('H', $time),
      $now->hour,
      'the time must same with this hour'
    );

        $this->assertEquals(
      date('i', $time),
      $now->minute,
      'the time must same with this minute'
    );

        $this->assertEquals(
      date('s', $time),
      $now->second,
      'the time must same with this second'
    );

        $this->assertEquals(
      date('l', $time),
      $now->dayName,
      'the time must same with this day name'
    );

        $this->assertEquals(
      date('F', $time),
      $now->monthName,
      'the time must same with this day name'
    );

        $this->assertTrue($now->isJul(), 'month must same');
        $this->assertTrue($now->isSaturday(), 'day must same');
    }

    /**
     * @test
     */
    public function itCorrectAge(): void
    {
        $now = new Now('01/01/2000');
        $this->assertSame(
      22.0,
      $now->age,
      'the age must equal'
    );
    }
}
