<?php

declare(strict_types=1);

namespace System\Test\Console;

use PHPUnit\Framework\TestCase;
use System\Console\Command;

class CommandTest extends TestCase
{
    /** @test */
    public function itCanGetWidth()
    {
        $command = new class([]) extends Command {
            public function width(int $min = 80, int $max = 160): int
            {
                return $this->getWidth($min, $max);
            }
        };

        $width = $command->width();
        $this->assertIsInt($width);
        $this->assertGreaterThan(79, $width);
        $this->assertLessThan(161, $width);
    }

    /** @test */
    public function itCanGetWidthUsingColumn()
    {
        $_ENV['COLUMNS'] = '100';
        $command         = new class([]) extends Command {
            public function width(int $min = 80, int $max = 160): int
            {
                return $this->getWidth($min, $max);
            }
        };

        $width = $command->width();
        $this->assertEquals(100, $width);
    }
}
