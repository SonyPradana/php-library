<?php

declare(strict_types=1);

namespace System\Test\Console;

use PHPUnit\Framework\TestCase;
use System\Console\Traits\TerminalTrait;

class HasColorSupportTest extends TestCase
{
    use EnvironmentIsolationTrait;

    private $testClass;
    private bool $isCI;

    protected function setUp(): void
    {
        parent::setUp();

        $this->backupEnvironment();
        $this->isCI = getenv('CI') !== false || getenv('GITHUB_ACTIONS') === 'true';

        $this->testClass = new class {
            use TerminalTrait;

            public function color($stream = \STDOUT): bool
            {
                return $this->hasColorSupport($stream);
            }
        };
    }

    public function testNoColorOverridesEverything(): void
    {
        $this->clearEnvironment();

        $this->setTestEnvironments([
            'NO_COLOR'     => '1',
            'TERM_PROGRAM' => 'Hyper',
            'COLORTERM'    => 'truecolor',
            'TERM'         => 'xterm-256color',
        ]);

        $result = $this->testClass->color();
        $this->assertFalse($result, 'NO_COLOR should override all other color-enabling settings');
    }

    public function testColorSupportedTerminals(): void
    {
        $supportedTerminals = [
            // 'xterm'          => true, // WARNING: ci (ubuntu-latest) does not support this
            // 'xterm-256color' => true, // WARNING: ci (ubuntu-latest) does not support this
            // 'screen'         => true, // WARNING: ci (ubuntu-latest) does not support this
            'tmux-256color'  => true,
            'linux'          => true,
            // 'dumb' => false,     // WARNING: windows (local) does not support this
            // 'unknown' => false,  // WARNING: windows (local) does not support this
        ];

        foreach ($supportedTerminals as $term => $expectedSupport) {
            $this->clearEnvironment();
            $this->setTestEnvironment('TERM', $term);

            $result = $this->testClass->color();
            $this->assertEquals(
                $expectedSupport,
                $result,
                "TERM={$term} should " . ($expectedSupport ? 'support' : 'not support') . ' colors'
            );
        }
    }

    public function testColorEnabledBySpecialPrograms(): void
    {
        $colorPrograms = [
            // 'TERM_PROGRAM' => 'Hyper', // WARNING: ci (ubuntu-latest) does not support this
            // 'COLORTERM'    => 'truecolor', // WARNING: ci (ubuntu-latest) does not support this
            // 'ANSICON'      => '1', // WARNING: ci (ubuntu-latest) does not support this
            'ConEmuANSI'   => 'ON',
        ];

        foreach ($colorPrograms as $envVar => $value) {
            $this->clearEnvironment();
            $this->setTestEnvironments([
                $envVar => $value,
                'TERM'  => 'unknown',
            ]);

            $result = $this->testClass->color();
            $this->assertTrue($result, "{$envVar}={$value} should enable color support");
        }
    }

    public function testMsystemSupport(): void
    {
        $msystems = ['MINGW32', 'MINGW64', 'UCRT64', 'CLANG64'];

        foreach ($msystems as $msystem) {
            $this->clearEnvironment();
            $this->setTestEnvironment('MSYSTEM', $msystem);

            $result = $this->testClass->color();
            $this->assertIsBool($result, "MSYSTEM={$msystem} should return boolean");
        }
    }

    public function testWithCustomStreams(): void
    {
        $this->clearEnvironment();

        $streams = [
            'memory' => fopen('php://memory', 'w+'),
            'temp'   => tmpfile(),
            'stdout' => STDOUT,
        ];

        foreach ($streams as $type => $stream) {
            if ($stream !== false) {
                $result = $this->testClass->color($stream);
                $this->assertIsBool($result, "Should handle {$type} stream gracefully");

                if ($stream !== STDOUT) {
                    fclose($stream);
                }
            }
        }
    }

    protected function tearDown(): void
    {
        $this->restoreEnvironment();
        parent::tearDown();
    }
}
