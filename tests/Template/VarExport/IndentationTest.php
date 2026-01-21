<?php

declare(strict_types=1);

namespace System\Tests\Template\VarExport;

use PHPUnit\Framework\TestCase;
use System\Template\VarExport;

/**
 * @covers \Savanna\System\Template\VarExport
 *
 * @testdox Skeleton Test for Indentation
 */
class IndentationTest extends TestCase
{
    /**
     * @test
     *
     * @testdox Uses 2-space indentation consistently
     */
    public function usesTwoSpaceIndentationConsistently(): void
    {
        $varExport = new VarExport();
        $varExport->setIndentation('  '); // Two spaces

        $array = [
            'key1' => 'value1',
            'key2' => [
                'nested_key1' => 'nested_value1',
                'nested_key2' => [
                    'double_nested_key' => 'double_nested_value',
                ],
            ],
            'key3' => 'value3',
        ];

        $output = $varExport->export($array);

        $expected = <<<'PHP'
[
  'key1' => 'value1',
  'key2' => [
    'nested_key1' => 'nested_value1',
    'nested_key2' => [
      'double_nested_key' => 'double_nested_value',
    ],
  ],
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
     * @testdox Uses 4-space indentation consistently
     */
    public function usesFourSpaceIndentationConsistently(): void
    {
        $varExport = new VarExport();
        $varExport->setIndentation('    '); // Four spaces

        $array = [
            'key1' => 'value1',
            'key2' => [
                'nested_key1' => 'nested_value1',
                'nested_key2' => [
                    'double_nested_key' => 'double_nested_value',
                ],
            ],
            'key3' => 'value3',
        ];

        $output = $varExport->export($array);

        $expected = <<<'PHP'
[
    'key1' => 'value1',
    'key2' => [
        'nested_key1' => 'nested_value1',
        'nested_key2' => [
            'double_nested_key' => 'double_nested_value',
        ],
    ],
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
     * @testdox Uses 8-space indentation consistently
     */
    public function usesEightSpaceIndentationConsistently(): void
    {
        $varExport = new VarExport();
        $varExport->setIndentation('        '); // Eight spaces

        $array = [
            'key1' => 'value1',
            'key2' => [
                'nested_key1' => 'nested_value1',
                'nested_key2' => [
                    'double_nested_key' => 'double_nested_value',
                ],
            ],
            'key3' => 'value3',
        ];

        $output = $varExport->export($array);

        $expected = <<<'PHP'
[
        'key1' => 'value1',
        'key2' => [
                'nested_key1' => 'nested_value1',
                'nested_key2' => [
                        'double_nested_key' => 'double_nested_value',
                ],
        ],
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
     * @testdox Uses 1-tab indentation consistently
     */
    public function usesOneTabIndentationConsistently(): void
    {
        $varExport = new VarExport();
        $varExport->setIndentation("\t"); // One tab

        $array = [
            'key1' => 'value1',
            'key2' => [
                'nested_key1' => 'nested_value1',
                'nested_key2' => [
                    'double_nested_key' => 'double_nested_value',
                ],
            ],
            'key3' => 'value3',
        ];

        $output = $varExport->export($array);

        $expected = <<<'PHP'
[
	'key1' => 'value1',
	'key2' => [
		'nested_key1' => 'nested_value1',
		'nested_key2' => [
			'double_nested_key' => 'double_nested_value',
		],
	],
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
     * @testdox Uses 2-tab indentation consistently
     */
    public function usesTwoTabIndentationConsistently(): void
    {
        $varExport = new VarExport();
        $varExport->setIndentation("\t\t"); // Two tabs

        $array = [
            'key1' => 'value1',
            'key2' => [
                'nested_key1' => 'nested_value1',
                'nested_key2' => [
                    'double_nested_key' => 'double_nested_value',
                ],
            ],
            'key3' => 'value3',
        ];

        $output = $varExport->export($array);

        $expected = <<<'PHP'
[
		'key1' => 'value1',
		'key2' => [
				'nested_key1' => 'nested_value1',
				'nested_key2' => [
						'double_nested_key' => 'double_nested_value',
				],
		],
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
     * @testdox Maintains nested array indentation levels
     */
    public function maintainsNestedArrayIndentationLevels(): void
    {
        $varExport = new VarExport();
        // Default indentation is 4 spaces

        $array = [
            'level1_key1' => 'value1',
            'level1_key2' => [
                'level2_key1' => 'value2',
                'level2_key2' => [
                    'level3_key1' => 'value3',
                    'level3_key2' => [
                        'level4_key1' => 'value4',
                    ],
                ],
            ],
            'level1_key3' => 'value5',
        ];

        $output = $varExport->export($array);

        $expected = <<<'PHP'
[
    'level1_key1' => 'value1',
    'level1_key2' => [
        'level2_key1' => 'value2',
        'level2_key2' => [
            'level3_key1' => 'value3',
            'level3_key2' => [
                'level4_key1' => 'value4',
            ],
        ],
    ],
    'level1_key3' => 'value5',
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
     * @testdox Normalizes closure indentation
     */
    public function normalizesClosureIndentation(): void
    {
        // $this->markTestSkipped('VarExport\'s closure extraction is not yet precise enough to normalize indentation correctly, as it extracts the entire line including variable assignments.');

        $varExport = new VarExport();
        $varExport->setIndentation('    '); // 4 spaces

        $array = [
            'closure' => function () {
                $a = 1;

                return $a;
            },
        ];

        $output = $varExport->export($array);

        $expected = <<<'PHP'
[
    'closure' => function () {
        $a = 1;

        return $a;
    },
]
PHP;
        // Normalize line endings for consistent comparison
        $normalizedOutput   = str_replace(["\r\n", "\r"], "\n", $output);
        $normalizedExpected = str_replace(["\r\n", "\r"], "\n", $expected);

        $this->assertEquals($normalizedExpected, $normalizedOutput);
    }

    /**
     * @test
     *
     * @testdox Handles mixed indentation in source
     */
    public function handlesMixedIndentationInSource(): void
    {
        // $this->markTestSkipped('VarExport does not expose a public API to re-indent arbitrary PHP code with mixed indentation. Its internal indentation normalization is tied to closure processing, which currently has pending issues (e.g., precise closure extraction).');

        $varExport = new VarExport();
        $varExport->setIndentation('    '); // 4 spaces

        $array = [
            'closure' => function () {
                $a = 1;
                $b = 2;

                return $a + $b;
            },
        ];

        $output = $varExport->export($array);

        $expected = <<<'PHP'
[
    'closure' => function () {
        $a = 1;
        $b = 2;

        return $a + $b;
    },
]
PHP;
        // Normalize line endings for consistent comparison
        $normalizedOutput   = str_replace(["\r\n", "\r"], "\n", $output);
        $normalizedExpected = str_replace(["\r\n", "\r"], "\n", $expected);

        $this->assertEquals($normalizedExpected, $normalizedOutput);
    }
}
