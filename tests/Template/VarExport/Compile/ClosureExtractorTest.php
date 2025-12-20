<?php

declare(strict_types=1);

namespace System\Tests\Template\VarExport\Compile;

use PHPUnit\Framework\TestCase;
use System\Template\VarExport\Compiler\ClosureExtractor;

/**
 * @covers \System\Template\VarExport\Compiler\ClosureExtractor
 *
 * @internal
 *
 * @testdox Tests for ClosureExtractor
 */
final class ClosureExtractorTest extends TestCase
{
    private ClosureExtractor $extractor;

    protected function setUp(): void
    {
        $this->extractor = new ClosureExtractor();
    }

    /**
     * @test
     *
     * @testdox Extract simple single-line closure without prefix
     */
    public function extractSimpleSingleLineClosureWithoutPrefix(): void
    {
        $closure = function () { return 'test'; };

        $reflection = new \ReflectionFunction($closure);
        $result     = $this->extractor->extract($reflection);

        // Check normalized output doesn't include key/arrow
        $this->assertStringNotContainsString("'closure'", $result['normalized']);
        $this->assertStringNotContainsString('=>', $result['normalized']);

        // Check it contains function keyword and body
        $this->assertStringContainsString('function', $result['normalized']);
        $this->assertStringContainsString("return 'test'", $result['normalized']);
    }

    /**
     * @test
     *
     * @testdox Extract simple single-line closure from array context
     */
    public function extractSimpleSingleLineClosureFromArrayContext(): void
    {
        // This simulates the problematic case from the issue
        $arrayWithClosure = [
            'closure' => function () {
                $a             = 1 + 2;
                $bolamasgakada = 1 + 2;

                echo $a;

                $b = 'text';

                // comment
                return 'Route::class';
            },
        ];

        // Get reflection of the closure
        $closure    = $arrayWithClosure['closure'];
        $reflection = new \ReflectionFunction($closure);
        $result     = $this->extractor->extract($reflection);

        // Verify no array key remains
        $normalized = $result['normalized'];
        $this->assertStringNotContainsString("'closure'", $normalized);
        $this->assertStringNotContainsString('=>', $normalized);

        // Verify closure structure is preserved
        $this->assertStringContainsString('function', $normalized);
        $this->assertStringContainsString('= 1 + 2', $normalized);
        $this->assertStringContainsString('bolamasgakada', $normalized);
        $this->assertStringContainsString('echo $a', $normalized);
        $this->assertStringContainsString('$b = \'text\'', $normalized);
        $this->assertStringContainsString('return \'Route::class\'', $normalized);
    }

    /**
     * @test
     *
     * @testdox Extract arrow function without prefix
     */
    public function extractArrowFunctionWithoutPrefix(): void
    {
        $closure = fn () => 'test';

        $reflection = new \ReflectionFunction($closure);
        $result     = $this->extractor->extract($reflection);

        // Check normalized output doesn't include array key/arrow prefix
        $this->assertStringNotContainsString("'closure'", $result['normalized']);

        // Check it contains fn keyword and => (which is part of arrow function syntax)
        $this->assertStringContainsString('fn', $result['normalized']);
        $this->assertStringContainsString("'test'", $result['normalized']);
    }

    /**
     * @test
     *
     * @testdox Extract multiline closure structure
     */
    public function extractMultilineClosureStructure(): void
    {
        $closure = function () {
            $x = 10;

            return $x * 2;
        };

        $reflection = new \ReflectionFunction($closure);
        $result     = $this->extractor->extract($reflection);

        $normalized = $result['normalized'];

        // Verify structure
        $this->assertStringContainsString('function', $normalized);
        $this->assertStringContainsString('$x = 10', $normalized);
        $this->assertStringContainsString('return $x * 2', $normalized);

        // Verify lines are array format
        $this->assertIsArray($result['lines']);
        $this->assertGreaterThan(0, count($result['lines']));
    }

    /**
     * @test
     *
     * @testdox Lines array doesn't contain duplicate closure key
     */
    public function linesArrayNoduplicateClosureKey(): void
    {
        $arrayWithClosure = [
            'closure' => function () { return 42; },
        ];

        $closure    = $arrayWithClosure['closure'];
        $reflection = new \ReflectionFunction($closure);
        $result     = $this->extractor->extract($reflection);

        $linesString = implode('', $result['lines']);

        // Should not have 'closure' key appearing
        // Count occurrences of 'closure' in the extracted code
        $closureKeyCount = substr_count($linesString, "'closure'");
        $this->assertEquals(0, $closureKeyCount, 'Closure key should not appear in extracted lines');
    }

    /**
     * @test
     *
     * @testdox Extract closure with parameters
     */
    public function extractClosureWithParameters(): void
    {
        $closure = function ($a, $b) {
            return $a + $b;
        };

        $reflection = new \ReflectionFunction($closure);
        $result     = $this->extractor->extract($reflection);

        $normalized = $result['normalized'];

        $this->assertStringContainsString('($a, $b)', $normalized);
        $this->assertStringContainsString('return $a + $b', $normalized);
    }

    /**
     * @test
     *
     * @testdox Extract closure with return type
     */
    public function extractClosureWithReturnType(): void
    {
        $closure = function (): int {
            return 5;
        };

        $reflection = new \ReflectionFunction($closure);
        $result     = $this->extractor->extract($reflection);

        $normalized = $result['normalized'];

        $this->assertStringContainsString(': int', $normalized);
        $this->assertStringContainsString('return 5', $normalized);
    }

    /**
     * @test
     *
     * @testdox Extract closure with mixed content and comments
     */
    public function extractClosureWithMixedContentAndComments(): void
    {
        $closure = function () {
            // This is a comment
            $result = 10;

            /* Block comment */
            return $result;
        };

        $reflection = new \ReflectionFunction($closure);
        $result     = $this->extractor->extract($reflection);

        $normalized = $result['normalized'];

        // Verify comments are preserved
        $this->assertStringContainsString('// This is a comment', $normalized);
        $this->assertStringContainsString('/* Block comment */', $normalized);
        $this->assertStringContainsString('$result = 10', $normalized);
    }

    /**
     * @test
     *
     * @testdox Metadata contains correct line information
     */
    public function metadataContainsCorrectLineInformation(): void
    {
        $closure = function () { return 'test'; };

        $reflection = new \ReflectionFunction($closure);
        $result     = $this->extractor->extract($reflection);

        $this->assertArrayHasKey('metadata', $result);
        $this->assertArrayHasKey('startLine', $result['metadata']);
        $this->assertArrayHasKey('endLine', $result['metadata']);
        $this->assertArrayHasKey('file', $result['metadata']);
        $this->assertArrayHasKey('isSingleLine', $result['metadata']);
        $this->assertArrayHasKey('isArrowFunction', $result['metadata']);

        $this->assertIsInt($result['metadata']['startLine']);
        $this->assertIsInt($result['metadata']['endLine']);
        $this->assertIsString($result['metadata']['file']);
        $this->assertIsBool($result['metadata']['isSingleLine']);
        $this->assertIsBool($result['metadata']['isArrowFunction']);
    }

    /**
     * @test
     *
     * @testdox Arrow function metadata correctly identified
     */
    public function arrowFunctionMetadataCorrectlyIdentified(): void
    {
        $closure = fn () => 42;

        $reflection = new \ReflectionFunction($closure);
        $result     = $this->extractor->extract($reflection);

        $this->assertTrue($result['metadata']['isArrowFunction']);
    }

    /**
     * @test
     *
     * @testdox Regular function metadata correctly identified
     */
    public function regularFunctionMetadataCorrectlyIdentified(): void
    {
        $closure = function () { return 42; };

        $reflection = new \ReflectionFunction($closure);
        $result     = $this->extractor->extract($reflection);

        $this->assertFalse($result['metadata']['isArrowFunction']);
    }

    /**
     * @test
     *
     * @testdox Normalized code has correct indentation removed
     */
    public function normalizedCodeHasCorrectIndentationRemoved(): void
    {
        // This closure is indented at runtime
        $closure = function () {
            return 'test';
        };

        $reflection = new \ReflectionFunction($closure);
        $result     = $this->extractor->extract($reflection);

        $lines = $result['lines'];

        // First line should be the function() declaration without leading spaces
        $firstLine = $lines[0];
        $this->assertEquals(0, strlen($firstLine) - strlen(ltrim($firstLine)),
            'First line should not have leading indentation');
    }

    /**
     * @test
     *
     * @testdox Validate single line rejects multiple closures
     */
    public function validateSingleLineRejectsMultipleClosures(): void
    {
        $line = 'function() {}, function() {}';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Multiple closures detected');

        $this->extractor->validateSingleLine($line, 42);
    }

    /**
     * @test
     *
     * @testdox Original code preserved in output
     */
    public function originalCodePreservedInOutput(): void
    {
        $closure = function () { return 'test'; };

        $reflection = new \ReflectionFunction($closure);
        $result     = $this->extractor->extract($reflection);

        $this->assertArrayHasKey('original', $result);
        $this->assertIsString($result['original']);
        $this->assertStringContainsString('function', $result['original']);
    }

    /**
     * @test
     *
     * @testdox Extract from complex array scenario
     */
    public function extractFromComplexArrayScenario(): void
    {
        $config = [
            'name'     => 'app',
            'handlers' => [
                'closure' => function () {
                    $a             = 1 + 2;
                    $bolamasgakada = 1 + 2;

                    echo $a;

                    $b = 'text';

                    // comment
                    return 'Route::class';
                },
                'other' => 'value',
            ],
        ];

        $closure    = $config['handlers']['closure'];
        $reflection = new \ReflectionFunction($closure);
        $result     = $this->extractor->extract($reflection);

        $normalized = $result['normalized'];

        // Should not contain any array-related syntax
        $this->assertStringNotContainsString("'closure'", $normalized);
        $this->assertStringNotContainsString('=>', $normalized);
        $this->assertStringNotContainsString("'other'", $normalized);

        // Should contain closure content
        $this->assertStringContainsString('function', $normalized);
        $this->assertStringContainsString('= 1 + 2', $normalized);
    }

    /**
     * @test
     *
     * @testdox Extract closure with trailing comma in array
     */
    public function extractClosureWithTrailingCommaInArray(): void
    {
        $arrayWithClosure = [
            'closure' => function () { return 'test'; },
        ];

        $closure    = $arrayWithClosure['closure'];
        $reflection = new \ReflectionFunction($closure);
        $result     = $this->extractor->extract($reflection);

        $normalized = $result['normalized'];

        // Should not have trailing comma (those belong to array context)
        $this->assertStringNotContainsString(',}', $normalized);
        // Should end with }
        $this->assertStringEndsWith('}', trim($normalized));
    }

    /**
     * @test
     *
     * @testdox Runtime closure throws exception
     */
    public function runtimeClosureThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        // eval() creates eval'd code file which doesn't exist as a real file
        $this->expectExceptionMessage('Source file not found');

        $closure    = eval('return function() { return "test"; };');
        $reflection = new \ReflectionFunction($closure);

        $this->extractor->extract($reflection);
    }

    /**
     * @test
     *
     * @testdox Non-existent file throws exception
     */
    public function nonExistentFileThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Source file not found');

        // Create a mock reflection with non-existent file
        $reflection = $this->createMock(\ReflectionFunction::class);
        $reflection->method('getFileName')->willReturn('/non/existent/file.php');
        $reflection->method('getStartLine')->willReturn(1);
        $reflection->method('getEndLine')->willReturn(1);

        $this->extractor->extract($reflection);
    }

    /**
     * @test
     *
     * @testdox AST contains correct structure
     */
    public function astContainsCorrectStructure(): void
    {
        $closure = function () { return 42; };

        $reflection = new \ReflectionFunction($closure);
        $result     = $this->extractor->extract($reflection);

        $this->assertArrayHasKey('ast', $result);
        $this->assertArrayHasKey('type', $result['ast']);
        $this->assertArrayHasKey('isArrowFunction', $result['ast']);
        $this->assertArrayHasKey('parameters', $result['ast']);
        $this->assertArrayHasKey('body', $result['ast']);

        $this->assertEquals('closure', $result['ast']['type']);
        $this->assertFalse($result['ast']['isArrowFunction']);
        $this->assertIsArray($result['ast']['parameters']);
    }
}
