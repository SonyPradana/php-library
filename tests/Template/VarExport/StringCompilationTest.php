<?php

declare(strict_types=1);

namespace System\Template\VarExport;

use PHPUnit\Framework\TestCase;
use System\Template\VarExport;

final class StringCompilationTest extends TestCase
{
    /**
     * @test
     *
     * @testdox Compiles string with unicode characters correctly
     */
    public function compilesStringWithUnicodeCharacters(): void
    {
        $varExport = new VarExport();
        $exported  = $varExport->export(['Hello, 世界']);

        $expected = <<<'PHP'
[
    0 => 'Hello, 世界',
]
PHP;

        $normalizedOutput   = str_replace("\r\n", "\n", $exported);

        $normalizedExpected = str_replace("\r\n", "\n", $expected);

        $this->assertEquals($normalizedExpected, $normalizedOutput);
    }

    /**
     * @test
     *
     * @testdox Compiles an empty string correctly
     */
    public function compilesEmptyString(): void
    {
        $varExport = new VarExport();
        $output    = $varExport->export(['']);

        $expected = <<<'PHP'
[
    0 => '',
]
PHP;
        // Normalize line endings to LF for consistent comparison
        $normalizedOutput   = str_replace("\r\n", "\n", $output);
        $normalizedExpected = str_replace("\r\n", "\n", $expected);

        $this->assertEquals($normalizedExpected, $normalizedOutput);
    }

    /**
     * @test
     *
     * @testdox Compiles string with single quotes correctly
     */
    public function compilesStringWithSingleQuotes(): void
    {
        $varExport = new VarExport();
        $output    = $varExport->export(["it's a string"]);

        $expected = <<<'PHP'
[
    0 => 'it\'s a string',
]
PHP;
        // Normalize line endings to LF for consistent comparison
        $normalizedOutput   = str_replace("\r\n", "\n", $output);
        $normalizedExpected = str_replace("\r\n", "\n", $expected);

        $this->assertEquals($normalizedExpected, $normalizedOutput);
    }

    /**
     * @test
     *
     * @testdox Compiles string with double quotes correctly
     */
    public function compilesStringWithDoubleQuotes(): void
    {
        $varExport = new VarExport();
        $output    = $varExport->export(['This is a "quoted" string']);

        $expected = <<<'PHP'
[
    0 => 'This is a "quoted" string',
]
PHP;
        // Normalize line endings to LF for consistent comparison
        $normalizedOutput   = str_replace("\r\n", "\n", $output);
        $normalizedExpected = str_replace("\r\n", "\n", $expected);

        $this->assertEquals($normalizedExpected, $normalizedOutput);
    }

    /**
     * @test
     *
     * @testdox Compiles string with backslashes correctly
     */
    public function compilesStringWithBackslashes(): void
    {
        $varExport = new VarExport();
        $output    = $varExport->export(['a\\b']);

        $expected = <<<'PHP'
[
    0 => 'a\\b',
]
PHP;
        // Normalize line endings to LF for consistent comparison
        $normalizedOutput   = str_replace("\r\n", "\n", $output);
        $normalizedExpected = str_replace("\r\n", "\n", $expected);

        $this->assertEquals($normalizedExpected, $normalizedOutput);
    }

    /**
     * @test
     *
     * @testdox Compiles string with special characters correctly
     */
    public function compilesStringWithSpecialCharacters(): void
    {
        $varExport = new VarExport();
        $output    = $varExport->export(['!@#$%^&*()-=_+[]{}|;:,.<>/?']);

        $expected = <<<'PHP'
[
    0 => '!@#$%^&*()-=_+[]{}|;:,.<>/?',
]
PHP;
        // Normalize line endings to LF for consistent comparison
        $normalizedOutput   = str_replace("\r\n", "\n", $output);
        $normalizedExpected = str_replace("\r\n", "\n", $expected);

        $this->assertEquals($normalizedExpected, $normalizedOutput);
    }

    /**
     * @test
     *
     * @testdox Compiles string with newlines correctly
     */
    public function compilesStringWithNewlines(): void
    {
        $varExport = new VarExport();
        $output    = $varExport->export(["First line\nSecond line"]);

        $expected = <<<'PHP'
[
    0 => 'First line
Second line',
]
PHP;
        // Normalize line endings to LF for consistent comparison
        $normalizedOutput   = str_replace("\r\n", "\n", $output);
        $normalizedExpected = str_replace("\r\n", "\n", $expected);

        $this->assertEquals($normalizedExpected, $normalizedOutput);
    }
}
