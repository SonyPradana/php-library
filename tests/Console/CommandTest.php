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

    private function resetEnv(): void
    {
        foreach (['NO_COLOR', 'TERM', 'TERM_PROGRAM', 'COLORTERM', 'ANSICON', 'ConEmuANSI', 'MSYSTEM'] as $var) {
            putenv($var);
        }
    }

    /** @test */
    public function itCanGetColorSupport(): void
    {
        $this->resetEnv();
        $command = new class([]) extends Command {
            public function color($stream = STDOUT): bool
            {
                return $this->hasColorSupport($stream);
            }
        };

        // Case 1: NO_COLOR
        putenv('NO_COLOR=1');
        $this->assertFalse($command->color());

        // Case 2: TERM matches pattern
        $this->resetEnv();
        putenv('TERM=xterm-256color');
        $this->assertTrue($command->color());

        // Case 3: TERM=dumb
        $this->resetEnv();
        putenv('TERM=dumb');
        $fp = fopen('php://temp', 'w');
        $this->assertFalse($command->color($fp));

        // Case 4: TERM_PROGRAM=Hyper
        $this->resetEnv();
        putenv('TERM_PROGRAM=Hyper');
        $this->assertTrue($command->color());

        // Case 5: COLORTERM set
        $this->resetEnv();
        putenv('COLORTERM=truecolor');
        $this->assertTrue($command->color());

        // Case 6: ANSICON set
        $this->resetEnv();
        putenv('ANSICON=1');
        $this->assertTrue($command->color());

        // Case 7: ConEmuANSI=ON
        $this->resetEnv();
        putenv('ConEmuANSI=ON');
        $this->assertTrue($command->color());

        // Case 8: MSYSTEM MinGW
        $this->resetEnv();
        putenv('MSYSTEM=MINGW64');
        putenv('TERM=xterm');
        $fp = fopen('php://temp', 'w');
        $this->assertTrue($command->color($fp));

        // Case 9: Windows VT100
        if (DIRECTORY_SEPARATOR === '\\'
            && function_exists('sapi_windows_vt100_support')
            && @stream_isatty(STDOUT)
            && @sapi_windows_vt100_support(STDOUT)
        ) {
            $this->resetEnv();
            $this->assertTrue($command->color());
        }
    }
}
