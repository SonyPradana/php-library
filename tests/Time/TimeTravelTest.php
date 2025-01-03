<?php

use PHPUnit\Framework\TestCase;
use System\Time\Exceptions\PropertyNotExist;
use System\Time\Exceptions\PropertyNotSetAble;
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
        $now->age;

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
            date('D'),
            $now->shortDay,
            'the time must same with this short day'
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
            date('D', $time),
            $now->shortDay,
            'the time must same with this short day'
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

    /** @test */
    public function itCalculatesAgeCorrectlyForTypicalBirthday(): void
    {
        $now         = new Now('01/01/1990');
        $currentYear = (int) date('Y');
        $expectedAge = $currentYear - 1990;
        $this->assertSame(
            $expectedAge,
            $now->age,
            'the age must equal'
        );
    }

    /** @test */
    public function itHandlesLeapYearBirthdayCorrectly(): void
    {
        $now             = new Now('02/29/2000');
        $birthDateBefore = new DateTime(date('02/29/2000'));
        $expectedAge     = $birthDateBefore->diff(new DateTime())->y;
        $this->assertSame(
            $expectedAge,
            $now->age,
            'the age must equal'
        );
    }

    /** @test */
    public function itHandlesFutureBirthdateCorrectly(): void
    {
        $now = new Now('next day');
        $this->assertSame(
            0,
            $now->age,
            'the age must be 0 for a future birthdate'
        );
    }

    /** @test */
    public function itCalculatesAgeAsZeroForTodayBirthdate(): void
    {
        $now = new Now('now', 'utc');
        $this->assertSame(
            0,
            $now->age,
            'the age must be 0 for today\'s birthdate'
        );
    }

    /** @test */
    public function itHandlesEdgeCasesAroundBirthdayCorrectly(): void
    {
        $nowBeforeBirthday = new Now(date('m/d/Y', strtotime('-1 day')));
        $birthDateBefore   = new DateTime(date('m/d/Y', strtotime('-1 day')));
        $expectedAgeBefore = $birthDateBefore->diff(new DateTime())->y;
        $this->assertSame(
            $expectedAgeBefore,
            $nowBeforeBirthday->age,
            'the age must be correct just before the birthday'
        );

        $nowAfterBirthday = new Now(date('m/d/Y', strtotime('+1 day')));
        $birthDateAfter   = new DateTime(date('m/d/Y', strtotime('+1 day')));
        $expectedAgeAfter = $birthDateAfter->diff(new DateTime())->y;
        $this->assertSame(
            $expectedAgeAfter,
            $nowAfterBirthday->age,
            'the age must be correct just after the birthday'
        );
    }

    /** @test */
    public function itHandlesDifferentTimeZonesCorrectly(): void
    {
        $nowUTC      = new Now('01/01/2000', 'UTC');
        $nowJKT      = new Now('01/01/2000', 'Asia/Jakarta');
        $birthDate   = new DateTime('01/01/2000');
        $expectedAge = $birthDate->diff(new DateTime())->y;

        $this->assertSame(
            $expectedAge,
            $nowUTC->age,
            'the age must be correct in UTC'
        );

        $this->assertSame(
            $expectedAge,
            $nowJKT->age,
            'the age must be correct in Asia/Jakarta'
        );
    }

    /** @test */
    public function itCanGetFromPrivateProperty()
    {
        $now = new Now();
        $now->day(12);

        $this->assertEquals(12, $now->day);
    }

    /** @test */
    public function itCanSetFromProperty()
    {
        $now      = new Now();
        $now->day = 12;

        $this->assertEquals(12, $now->day);
    }

    /** @test */
    public function itCanUsePrivatePropertyUsingSetterAndGetter()
    {
        $now = new Now();

        $now->year = 2022;
        $this->assertEquals(2022, $now->year);

        $now->month = 1;
        $this->assertEquals(1, $now->month);

        $now->day = 11;
        $this->assertEquals(11, $now->day);

        $now->hour = 1;
        $this->assertEquals(1, $now->hour);

        $now->minute = 27;
        $this->assertEquals(27, $now->minute);

        $now->second = 0;
        $this->assertEquals(0, $now->second);

        $this->assertEquals('January', $now->monthName);
        $this->assertEquals('Tuesday', $now->dayName);
        $this->assertEquals('Tue', $now->shortDay);
        $this->assertEquals('Asia/Jakarta', $now->timeZone);

        $this->lessThan($now->age);
    }

    /** @test */
    public function itThrowWhenSetPrivatePropertyAndNotSetable()
    {
        $now            = new Now();

        $this->expectException(PropertyNotSetAble::class);
        $now->timestamp = time();

        $this->expectException(PropertyNotSetAble::class);
        $now->monthName = 'June';

        $this->expectException(PropertyNotSetAble::class);
        $now->dayName = 'Tuesday';

        $this->expectException(PropertyNotSetAble::class);
        $now->timeZone = 'Asia/Jakarta';

        $this->expectException(PropertyNotSetAble::class);
        $now->age = 27;
    }

    /** @test */
    public function itThrowWhenGetUndefineProperty()
    {
        $now = new Now();

        $this->expectException(PropertyNotExist::class);
        $now->not_exist_property;
    }

    /** @test */
    public function itCanReturnFormatedTime()
    {
        $now = new Now('29-01-2023');

        $this->assertEquals('2023-01-29', $now->format('Y-m-d'));
    }

    /** @test */
    public function itCanReturnFormatedTimeWithStandartTime()
    {
        $now = new Now('29-01-2023', 'UTC');

        $this->assertEquals('Sunday, 29-Jan-2023 00:00:00 UTC', $now->formatCOOKIE());
    }
}
