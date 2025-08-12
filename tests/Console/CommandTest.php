<?php

declare(strict_types=1);

namespace System\Test\Console;

use PHPUnit\Framework\TestCase;
use System\Console\Command;

class CommandTest extends TestCase
{
    protected function setUp(): void
    {
        $this->resetEnv();
    }

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
    public function itDisablesWhenNoColor(): void
    {
        $cmd = new class([]) extends Command {
            public function color($stream = STDOUT): bool
            {
                return $this->hasColorSupport($stream);
            }
        };

        putenv('NO_COLOR=1');
        $this->assertFalse($cmd->color());
    }

    /** @test */
    public function itMatchesTermPattern(): void
    {
        if (!@stream_isatty(STDOUT)) {
            $this->markTestSkipped('Not a TTY, TERM match pattern test skipped');
        }

        $cmd = new class([]) extends Command {
            public function color($stream = STDOUT): bool
            {
                return $this->hasColorSupport($stream);
            }
        };

        putenv('TERM=xterm-256color');
        $this->assertTrue($cmd->color());
    }

    /** @test */
    public function itDisablesWhenTermDumb(): void
    {
        $cmd = new class([]) extends Command {
            public function color($stream = STDOUT): bool
            {
                return $this->hasColorSupport($stream);
            }
        };

        putenv('TERM=dumb');
        $fp = fopen('php://temp', 'w'); // not a TTY
        $this->assertFalse($cmd->color($fp));
    }

    /** @test */
    public function itMatchesTermProgramHyper(): void
    {
        $cmd = new class([]) extends Command {
            public function color($stream = STDOUT): bool
            {
                return $this->hasColorSupport($stream);
            }
        };

        putenv('TERM_PROGRAM=Hyper');
        $this->assertTrue($cmd->color());
    }

    /** @test */
    public function itMatchesColorterm(): void
    {
        $cmd = new class([]) extends Command {
            public function color($stream = STDOUT): bool
            {
                return $this->hasColorSupport($stream);
            }
        };

        putenv('COLORTERM=truecolor');
        $this->assertTrue($cmd->color());
    }

    /** @test */
    public function itMatchesAnsicon(): void
    {
        $cmd = new class([]) extends Command {
            public function color($stream = STDOUT): bool
            {
                return $this->hasColorSupport($stream);
            }
        };

        putenv('ANSICON=1');
        $this->assertTrue($cmd->color());
    }

    /** @test */
    public function itMatchesConEmuAnsi(): void
    {
        $cmd = new class([]) extends Command {
            public function color($stream = STDOUT): bool
            {
                return $this->hasColorSupport($stream);
            }
        };

        putenv('ConEmuANSI=ON');
        $this->assertTrue($cmd->color());
    }

    /** @test */
    public function itMatchesMsSystemMingw(): void
    {
        $cmd = new class([]) extends Command {
            public function color($stream = STDOUT): bool
            {
                return $this->hasColorSupport($stream);
            }
        };

        putenv('MSYSTEM=MINGW64');
        putenv('TERM=xterm'); // ensure regex matches
        $fp = fopen('php://temp', 'w'); // not a TTY
        $this->assertTrue($cmd->color($fp));
    }

    /** @test */
    public function itMatchesWindowsVt100(): void
    {
        if (DIRECTORY_SEPARATOR !== '\\'
            || !function_exists('sapi_windows_vt100_support')
            || !@stream_isatty(STDOUT)
            || !@sapi_windows_vt100_support(STDOUT)
        ) {
            $this->markTestSkipped('Windows VT100 not supported in this environment');
        }

        $cmd = new class([]) extends Command {
            public function color($stream = STDOUT): bool
            {
                return $this->hasColorSupport($stream);
            }
        };
        $this->assertTrue($cmd->color());
    }
}
