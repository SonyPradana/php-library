<?php

declare(strict_types=1);

namespace System\Template\VarExport;

use PHPUnit\Framework\TestCase;
use System\Template\VarExport;
use System\Template\VarExport\Value\Constant;

/**
 * @covers \Savanna\System\Template\VarExport
 *
 * @testdox Skeleton Test for Array Compilation
 */
class ArrayCompilationTest extends TestCase
{
    /**
     * @test
     *
     * @testdox Compiles an empty array correctly
     */
    public function compilesEmptyArray(): void
    {
        $varExport = new VarExport();
        $output    = $varExport->export([]);

        $expected = <<<'PHP'
[]
PHP;
        // Normalize line endings to LF for consistent comparison
        $normalizedOutput   = str_replace(["\r\n", "\r"], "\n", $output);
        $normalizedExpected = str_replace(["\r\n", "\r"], "\n", $expected);

        $this->assertEquals($normalizedExpected, $normalizedOutput);
    }

    /**
     * @test
     *
     * @testdox Compiles an indexed array correctly
     */
    public function compilesIndexedArray(): void
    {
        $varExport = new VarExport();
        $output    = $varExport->export([1, 2, 3]);

        $expected = <<<'PHP'
[
    0 => 1,
    1 => 2,
    2 => 3,
]
PHP;
        // Normalize line endings to LF for consistent comparison
        $normalizedOutput   = str_replace(["\r\n", "\r"], "\n", $output);
        $normalizedExpected = str_replace(["\r\n", "\r"], "\n", $expected);

        $this->assertEquals($normalizedExpected, $normalizedOutput);
    }

    /**
     * @test
     *
     * @testdox Compiles an associative array correctly
     */
    public function compilesAssociativeArray(): void
    {
        $varExport = new VarExport();
        $output    = $varExport->export([
            'name'    => 'Savanna',
            'version' => '1.0',
        ]);

        $expected = <<<'PHP'
[
    'name' => 'Savanna',
    'version' => '1.0',
]
PHP;
        // Normalize line endings to LF for consistent comparison
        $normalizedOutput   = str_replace(["\r\n", "\r"], "\n", $output);
        $normalizedExpected = str_replace(["\r\n", "\r"], "\n", $expected);

        $this->assertEquals($normalizedExpected, $normalizedOutput);
    }

    /**
     * @test
     *
     * @testdox Compiles a mixed array correctly
     */
    public function compilesMixedArray(): void
    {
        $varExport = new VarExport();
        $output    = $varExport->export([
            0         => 'first',
            'name'    => 'Savanna',
            1         => 'second',
            'version' => '1.0',
        ]);

        $expected = <<<'PHP'
[
    0 => 'first',
    'name' => 'Savanna',
    1 => 'second',
    'version' => '1.0',
]
PHP;
        // Normalize line endings to LF for consistent comparison
        $normalizedOutput   = str_replace(["\r\n", "\r"], "\n", $output);
        $normalizedExpected = str_replace(["\r\n", "\r"], "\n", $expected);

        $this->assertEquals($normalizedExpected, $normalizedOutput);
    }

    /**
     * @test
     *
     * @testdox Compiles array with non-sequential numeric keys
     */
    public function compilesArrayWithNonSequentialKeys(): void
    {
        $varExport = new VarExport();
        $output    = $varExport->export([
            0 => 'first',
            2 => 'third',
            1 => 'second',
        ]);

        $expected = <<<'PHP'
[
    0 => 'first',
    2 => 'third',
    1 => 'second',
]
PHP;
        // Normalize line endings to LF for consistent comparison
        $normalizedOutput   = str_replace(["\r\n", "\r"], "\n", $output);
        $normalizedExpected = str_replace(["\r\n", "\r"], "\n", $expected);

        $this->assertEquals($normalizedExpected, $normalizedOutput);
    }

    /**
     * @test
     *
     * @testdox Compiles nested arrays (depth 2) correctly
     */
    public function compilesNestedArraysDepth2(): void
    {
        $varExport = new VarExport();
        $output    = $varExport->export([
            'level1_key1' => 'value1',
            'level1_key2' => [
                'level2_key1' => 'value2',
                'level2_key2' => 123,
            ],
            'level1_key3' => true,
        ]);

        $expected = <<<'PHP'
[
    'level1_key1' => 'value1',
    'level1_key2' => [
        'level2_key1' => 'value2',
        'level2_key2' => 123,
    ],
    'level1_key3' => true,
]
PHP;
        // Normalize line endings to LF for consistent comparison
        $normalizedOutput   = str_replace(["\r\n", "\r"], "\n", $output);
        $normalizedExpected = str_replace(["\r\n", "\r"], "\n", $expected);

        $this->assertEquals($normalizedExpected, $normalizedOutput);
    }

    /**
     * @test
     *
     * @testdox Compiles nested arrays (depth 5) correctly
     */
    public function compilesNestedArraysDepth5(): void
    {
        $varExport = new VarExport();
        $output    = $varExport->export([
            'l1k1' => [
                'l2k1' => [
                    'l3k1' => [
                        'l4k1' => [
                            'l5k1' => 'deep_value',
                        ],
                    ],
                ],
            ],
            'l1k2' => 'value',
        ]);

        $expected = <<<'PHP'
[
    'l1k1' => [
        'l2k1' => [
            'l3k1' => [
                'l4k1' => [
                    'l5k1' => 'deep_value',
                ],
            ],
        ],
    ],
    'l1k2' => 'value',
]
PHP;
        // Normalize line endings to LF for consistent comparison
        $normalizedOutput   = str_replace(["\r\n", "\r"], "\n", $output);
        $normalizedExpected = str_replace(["\r\n", "\r"], "\n", $expected);

        $this->assertEquals($normalizedExpected, $normalizedOutput);
    }

    /**
     * @test
     *
     * @testdox Compiles array with various value types mixed
     */
    public function compilesArrayWithMixedValueTypes(): void
    {
        $varExport = new VarExport();
        $output    = $varExport->export([
            'string'       => 'hello',
            'integer'      => 123,
            'float'        => 1.23,
            'boolean'      => true,
            'null'         => null,
            'nested_array' => [
                'key' => 'value',
            ],
        ]);

        $expected = <<<'PHP'
[
    'string' => 'hello',
    'integer' => 123,
    'float' => 1.23,
    'boolean' => true,
    'null' => null,
    'nested_array' => [
        'key' => 'value',
    ],
]
PHP;
        // Normalize line endings to LF for consistent comparison
        $normalizedOutput   = str_replace(["\r\n", "\r"], "\n", $output);
        $normalizedExpected = str_replace(["\r\n", "\r"], "\n", $expected);

        $this->assertEquals($normalizedExpected, $normalizedOutput);
    }

    /**
     * @test
     *
     * @testdox Compiles a large array correctly
     */
    public function compilesLargeArray(): void
    {
        $varExport = new VarExport();

        $largeArray = [];
        for ($i = 0; $i < 1000; $i++) {
            $largeArray['key_' . $i] = 'value_' . $i;
        }

        $output = $varExport->export($largeArray);

        // Generate expected output in VarExport's format
        $expectedParts = [];
        foreach ($largeArray as $key => $value) {
            $expectedParts[] = sprintf("    '%s' => '%s',", $key, $value);
        }
        $expected = "[\n" . implode("\n", $expectedParts) . "\n]";
        // Normalize line endings to LF for consistent comparison
        $normalizedOutput   = str_replace(["\r\n", "\r"], "\n", $output);
        $normalizedExpected = str_replace(["\r\n", "\r"], "\n", $expected);

        $this->assertEquals($normalizedExpected, $normalizedOutput);
    }

    /**
     * @test
     *
     * @testdox Compiles an array containing a reference
     */
    public function compilesArrayWithReference(): void
    {
        $varExport = new VarExport();

        $refValue = 'original';
        $array    = [
            'key1' => 'value1',
            'key2' => &$refValue,
            'key3' => 'value3',
        ];

        $output = $varExport->export($array);

        // Note: var_export handles references by value, which is the desired behavior for VarExport
        $expected = <<<'PHP'
[
    'key1' => 'value1',
    'key2' => 'original',
    'key3' => 'value3',
]
PHP;
        // Normalize line endings to LF for consistent comparison
        $normalizedOutput   = str_replace(["\r\n", "\r"], "\n", $output);
        $normalizedExpected = str_replace(["\r\n", "\r"], "\n", $expected);

        $this->assertEquals($normalizedExpected, $normalizedOutput);
    }

    /**
     * @test
     *
     * @testdox Compiles an array containing a constant
     */
    public function compilesArrayWithConstant(): void
    {
        $varExport = new VarExport();
        $output    = $varExport->export(['my_constant' => new Constant('MY_TEST_CONSTANT')]);

        $expected = <<<'PHP'
[
    'my_constant' => MY_TEST_CONSTANT,
]
PHP;
        // Normalize line endings to LF for consistent comparison
        $normalizedOutput   = str_replace(["\r\n", "\r"], "\n", $output);
        $normalizedExpected = str_replace(["\r\n", "\r"], "\n", $expected);

        $this->assertEquals($normalizedExpected, $normalizedOutput);
    }
}
