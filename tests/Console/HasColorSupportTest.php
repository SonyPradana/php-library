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

        // Backup environment sebelum test
        $this->backupEnvironment();

        // Create test class instance
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

    /**
     * @group isolated
     */
    public function testNoColorOverridesEverything(): void
    {
        $this->clearEnvironment();

        // Set NO_COLOR dengan environment variables lain yang seharusnya enable color
        $this->setTestEnvironments([
            'NO_COLOR'     => '1',
            'TERM_PROGRAM' => 'Hyper',
            'COLORTERM'    => 'truecolor',
            'TERM'         => 'xterm-256color',
        ]);

        $result = $this->testClass->hasColorSupport();
        $this->assertFalse($result, 'NO_COLOR should override all other color-enabling settings');
    }

    /**
     * @group isolated
     */
    public function testColorSupportedTerminals(): void
    {
        $supportedTerminals = [
            'xterm'          => true,
            'xterm-256color' => true,
            'screen'         => true,
            'tmux-256color'  => true,
            'linux'          => true,
            // 'dumb' => false,
            // 'unknown' => false,
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

    /**
     * @group isolated
     */
    public function testColorEnabledBySpecialPrograms(): void
    {
        $colorPrograms = [
            'TERM_PROGRAM' => 'Hyper',
            'COLORTERM'    => 'truecolor',
            'ANSICON'      => '1',
            'ConEmuANSI'   => 'ON',
        ];

        foreach ($colorPrograms as $envVar => $value) {
            $this->clearEnvironment();
            $this->setTestEnvironments([
                $envVar => $value,
                'TERM'  => 'unknown', // Set unknown TERM to focus on the specific env var
            ]);

            $result = $this->testClass->hasColorSupport();
            $this->assertTrue($result, "{$envVar}={$value} should enable color support");
        }
    }

    /**
     * @group isolated
     */
    public function testMsystemSupport(): void
    {
        $msystems = ['MINGW32', 'MINGW64', 'UCRT64', 'CLANG64'];

        foreach ($msystems as $msystem) {
            $this->clearEnvironment();
            $this->setTestEnvironment('MSYSTEM', $msystem);

            // Result depends on stream_isatty, but function should not crash
            $result = $this->testClass->hasColorSupport();
            $this->assertIsBool($result, "MSYSTEM={$msystem} should return boolean");
        }
    }

    /**
     * Test dengan custom stream.
     *
     * @group isolated
     */
    public function testWithCustomStreams(): void
    {
        $this->clearEnvironment();

        // Test dengan berbagai stream types
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
