<?php

declare(strict_types=1);

namespace System\Template\VarExport;

use PHPUnit\Framework\TestCase;
use System\Template\VarExport;

/**
 * @covers \Savanna\System\Template\VarExport
 *
 * @testdox Skeleton Test for Basic Type Compilation
 */
class BasicTypesTest extends TestCase
{
    /**
     * @test
     *
     * @testdox Compiles a positive integer correctly
     */
    public function compilesPositiveInteger(): void
    {
        $varExport = new VarExport();
        $output    = $varExport->export([123]);

        $expected = <<<'PHP'
[
    123,
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
     * @testdox Compiles a negative integer correctly
     */
    public function compilesNegativeInteger(): void
    {
        $varExport = new VarExport();
        $output    = $varExport->export([-456]);

        $expected = <<<'PHP'
[
    -456,
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
     * @testdox Compiles a zero integer correctly
     */
    public function compilesZeroInteger(): void
    {
        $varExport = new VarExport();
        $output    = $varExport->export([0]);

        $expected = <<<'PHP'
[
    0,
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
     * @testdox Compiles a positive float correctly
     */
    public function compilesPositiveFloat(): void
    {
        $varExport = new VarExport();
        $output    = $varExport->export([123.45]);

        $expected = <<<'PHP'
[
    123.45,
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
     * @testdox Compiles a negative float correctly
     */
    public function compilesNegativeFloat(): void
    {
        $varExport = new VarExport();
        $output    = $varExport->export([-67.89]);

        $expected = <<<'PHP'
[
    -67.89,
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
     * @testdox Compiles a float with a decimal part correctly
     */
    public function compilesFloatWithDecimal(): void
    {
        $varExport = new VarExport();
        $output    = $varExport->export([10.0]);

        $expected = <<<'PHP'
[
    10.0,
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
     * @testdox Compiles a whole number float correctly
     */
    public function compilesWholeNumberFloat(): void
    {
        $varExport = new VarExport();
        $output    = $varExport->export([10.0]);

        $expected = <<<'PHP'
[
    10.0,
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
     * @testdox Compiles a float in scientific notation correctly
     */
    public function compilesFloatScientificNotation(): void
    {
        $varExport = new VarExport();
        $output    = $varExport->export([1.23e4]);

        $expected = <<<'PHP'
[
    12300.0,
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
     * @testdox Compiles a boolean true correctly
     */
    public function compilesBooleanTrue(): void
    {
        $varExport = new VarExport();
        $output    = $varExport->export([true]);

        $expected = <<<'PHP'
[
    true,
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
     * @testdox Compiles a boolean false correctly
     */
    public function compilesBooleanFalse(): void
    {
        $varExport = new VarExport();
        $output    = $varExport->export([false]);

        $expected = <<<'PHP'
[
    false,
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
     * @testdox Compiles null correctly
     */
    public function compilesNull(): void
    {
        $varExport = new VarExport();
        $output    = $varExport->export([null]);

        $expected = <<<'PHP'
[
    null,
]
PHP;
        // Normalize line endings to LF for consistent comparison
        $normalizedOutput   = str_replace("\r\n", "\n", $output);
        $normalizedExpected = str_replace("\r\n", "\n", $expected);

        $this->assertEquals($normalizedExpected, $normalizedOutput);
    }
}
