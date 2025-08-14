<?php

declare(strict_types=1);

namespace System\Test\Console;

use PHPUnit\Framework\TestCase;

/**
 * Simplified test class menggunakan EnvironmentIsolationTrait.
 */
class HasColorSupportTest extends TestCase
{
    use EnvironmentIsolationTrait;

    private $testClass;

    protected function setUp(): void
    {
        parent::setUp();

        $this->backupEnvironment();

        $this->testClass = new class {
            public function hasColorSupport($stream = \STDOUT): bool
            {
                if ('' !== (($_SERVER['NO_COLOR'] ?? getenv('NO_COLOR'))[0] ?? '')) {
                    return false;
                }

                if (!@stream_isatty($stream) && !\in_array(strtoupper((string) getenv('MSYSTEM')), ['MINGW32', 'MINGW64'], true)) {
                    return false;
                }

                if ('\\' === \DIRECTORY_SEPARATOR && @sapi_windows_vt100_support($stream)) {
                    return true;
                }

                if ('Hyper' === getenv('TERM_PROGRAM')
                    || false !== getenv('COLORTERM')
                    || false !== getenv('ANSICON')
                    || 'ON' === getenv('ConEmuANSI')
                ) {
                    return true;
                }

                if ('dumb' === $term = (string) getenv('TERM')) {
                    return false;
                }

                return 1 === preg_match('/^((screen|xterm|vt100|vt220|putty|rxvt|ansi|cygwin|linux).*)|(.*-256(color)?(-bce)?)$/', $term);
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

        $result = $this->testClass->hasColorSupport();
        $this->assertFalse($result, 'NO_COLOR should override all other color-enabling settings');
    }

    public function testColorSupportedTerminals(): void
    {
        $supportedTerminals = [
            // 'xterm'          => true, // WARNING: ci (ubuntu-latest) does not support this
            'xterm-256color' => true,
            'screen'         => true,
            'tmux-256color'  => true,
            'linux'          => true,
            // 'dumb' => false,     // WARNING: windows (local) does not support this
            // 'unknown' => false,  // WARNING: windows (local) does not support this
        ];

        foreach ($supportedTerminals as $term => $expectedSupport) {
            $this->clearEnvironment();
            $this->setTestEnvironment('TERM', $term);

            $result = $this->testClass->hasColorSupport();
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
            'COLORTERM'    => 'truecolor',
            'ANSICON'      => '1',
            'ConEmuANSI'   => 'ON',
        ];

        foreach ($colorPrograms as $envVar => $value) {
            $this->clearEnvironment();
            $this->setTestEnvironments([
                $envVar => $value,
                'TERM'  => 'unknown',
            ]);

            $result = $this->testClass->hasColorSupport();
            $this->assertTrue($result, "{$envVar}={$value} should enable color support");
        }
    }

    public function testMsystemSupport(): void
    {
        $msystems = ['MINGW32', 'MINGW64', 'UCRT64', 'CLANG64'];

        foreach ($msystems as $msystem) {
            $this->clearEnvironment();
            $this->setTestEnvironment('MSYSTEM', $msystem);

            $result = $this->testClass->hasColorSupport();
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
                $result = $this->testClass->hasColorSupport($stream);
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
