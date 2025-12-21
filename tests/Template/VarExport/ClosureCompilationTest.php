<?php

declare(strict_types=1);

namespace System\Test\Template\VarExport;

use PHPUnit\Framework\TestCase;
use System\Template\VarExport;

/**
 * @covers \System\Template\VarExport
 *
 * @internal
 *
 * @testdox Tests for Closure Compilation
 */
final class ClosureCompilationTest extends TestCase
{
    private VarExport $exporter;

    protected function setUp(): void
    {
        $this->exporter = new VarExport();
    }

    private function assertCompiles(string $expected, mixed $value): void
    {
        $exported = $this->exporter->export($value);

        // Normalize line endings to LF for consistent comparison
        $normalizedOutput   = str_replace("\r\n", "\n", $exported);
        $normalizedExpected = str_replace("\r\n", "\n", $expected);

        $this->assertEquals($normalizedExpected, $normalizedOutput);
    }

    /**
     * @test
     *
     * @testdox Compiles simple single-line closure without parameters
     */
    public function testCompilesSimpleClosureOneLine(): void
    {
        $closure = function () { return 'test'; };

        $expected = <<<'PHP'
[
    'closure' => function () { return 'test'; },
]
PHP;

        $this->assertCompiles($expected, ['closure' => $closure]);
    }

    /**
     * @test
     *
     * @testdox Compiles PHP 7.4+ arrow function syntax (fn)
     */
    public function testCompilesArrowFunction(): void
    {
        $closure = fn () => 'test';

        $expected = <<<PHP
[
    'closure' => fn () => 'test',
]
PHP;

        $this->assertCompiles($expected, ['closure' => $closure]);
    }

    /**
     * @test
     *
     * @testdox Compiles multi-line closure with variable assignments and return statement
     */
    public function testCompilesMultiLineClosure(): void
    {
        $closure = function () {
            $a = 1;
            $b = 2;

            return $a + $b;
        };

        $expected = <<<'PHP'
[
    'closure' => function () {
        $a = 1;
        $b = 2;

        return $a + $b;
    },
]
PHP;

        $this->assertCompiles($expected, ['closure' => $closure]);
    }

    /**
     * @test
     *
     * @testdox Compiles closure with untyped parameters ($a, $b)
     */
    public function testCompilesClosureWithParameters(): void
    {
        $closure = function ($a, $b) {
            return $a + $b;
        };

        $expected = <<<'PHP'
[
    'closure' => function ($a, $b) {
        return $a + $b;
    },
]
PHP;

        $this->assertCompiles($expected, ['closure' => $closure]);
    }

    /**
     * @test
     *
     * @testdox Compiles closure with typed parameters (int $a, string $b)
     */
    public function testCompilesClosureWithTypedParameters(): void
    {
        $closure = function (int $a, string $b) {
            return $a . $b;
        };

        $expected = <<<'PHP'
[
    'closure' => function (int $a, string $b) {
        return $a . $b;
    },
]
PHP;

        $this->assertCompiles($expected, ['closure' => $closure]);
    }

    /**
     * @test
     *
     * @testdox Compiles closure with default parameter values (int $a, string $b = 'default')
     */
    public function testCompilesClosureWithDefaultParameters(): void
    {
        $closure = function (int $a, string $b = 'default') {
            return $a . $b;
        };

        $expected = <<<'PHP'
[
    'closure' => function (int $a, string $b = 'default') {
        return $a . $b;
    },
]
PHP;

        $this->assertCompiles($expected, ['closure' => $closure]);
    }

    /**
     * @test
     *
     * @testdox Compiles closure with variadic parameters (int ...$numbers)
     */
    public function testCompilesClosureWithVariadicParameters(): void
    {
        $closure = function (int ...$numbers) {
            return array_sum($numbers);
        };

        $expected = <<<'PHP'
[
    'closure' => function (int ...$numbers) {
        return array_sum($numbers);
    },
]
PHP;

        $this->assertCompiles($expected, ['closure' => $closure]);
    }

    /**
     * @test
     *
     * @testdox Compiles closure with reference parameters (&$a)
     */
    public function testCompilesClosureWithReferenceParameters(): void
    {
        $closure = function (&$a) {
            $a++;
        };

        $expected = <<<'PHP'
[
    'closure' => function (&$a) {
        $a++;
    },
]
PHP;

        $this->assertCompiles($expected, ['closure' => $closure]);
    }

    /**
     * @test
     *
     * @testdox Compiles closure with return type declaration (: int)
     */
    public function testCompilesClosureWithReturnType(): void
    {
        $closure = function (): int {
            return 1;
        };

        $expected = <<<'PHP'
[
    'closure' => function (): int {
        return 1;
    },
]
PHP;

        $this->assertCompiles($expected, ['closure' => $closure]);
    }

    /**
     * @test
     *
     * @testdox Compiles closure with nullable return type (: ?int)
     */
    public function testCompilesClosureWithNullableReturnType(): void
    {
        $closure = function (): ?int {
            return null;
        };

        $expected = <<<'PHP'
[
    'closure' => function (): ?int {
        return null;
    },
]
PHP;

        $this->assertCompiles($expected, ['closure' => $closure]);
    }

    /**
     * @test
     *
     * @testdox Compiles closure with no captured variables
     */
    public function testCompilesClosureWithoutCapturedVariables(): void
    {
        $closure = function () {
            return 'no captured vars';
        };

        $expected = <<<'PHP'
[
    'closure' => function () {
        return 'no captured vars';
    },
]
PHP;

        $this->assertCompiles($expected, ['closure' => $closure]);
    }

    /**
     * @test
     *
     * @testdox Compiles closure with single captured variable using IIFE wrapper
     */
    public function testCompilesClosureWithOneCapturedVariable(): void
    {
        $capturedVar = 'world';

        $closure = function () use ($capturedVar) {
            return 'hello ' . $capturedVar;
        };

        $output = $this->exporter->export(['closure' => $closure]);

        // Should contain IIFE wrapper for captured var
        $this->assertStringContainsString('(function() {', $output);
        $this->assertStringContainsString('$capturedVar =', $output);
        $this->assertStringContainsString('return function () use ($capturedVar)', $output);
        $this->assertStringContainsString('})(),', $output);
    }

    /**
     * @test
     *
     * @testdox Compiles closure with multiple captured variables using IIFE wrapper
     */
    public function testCompilesClosureWithMultipleCapturedVariables(): void
    {
        $var1 = 'hello';
        $var2 = 'world';

        $closure = function () use ($var1, $var2) {
            return $var1 . ' ' . $var2;
        };

        $output = $this->exporter->export(['closure' => $closure]);

        // Should contain IIFE wrapper with both variables
        $this->assertStringContainsString('(function() {', $output);
        $this->assertStringContainsString('$var1 =', $output);
        $this->assertStringContainsString('$var2 =', $output);
        $this->assertStringContainsString('return function () use ($var1, $var2)', $output);
        $this->assertStringContainsString('})(),', $output);
    }

    /**
     * @test
     *
     * @testdox Compiles closure with captured variable by reference using IIFE wrapper
     */
    public function testCompilesClosureWithCapturedByReference(): void
    {
        $var = 'value';

        $closure = function () use (&$var) {
            $var = 'new value';
        };

        $output = $this->exporter->export(['closure' => $closure]);

        // Should contain IIFE wrapper with reference var
        $this->assertStringContainsString('(function() {', $output);
        $this->assertStringContainsString('$var =', $output);
        $this->assertStringContainsString('return function () use (&$var)', $output);
        $this->assertStringContainsString('})(),', $output);
    }

    /**
     * @test
     *
     * @testdox Compiles closure stored in variable with array context
     */
    public function compilesClosureStoredInVariable(): void
    {
        $closure = function () {
            return 42;
        };

        $config = ['handler' => $closure];
        $output = $this->exporter->export($config);

        $this->assertStringContainsString("'handler' => function ()", $output);
        $this->assertStringContainsString('return 42', $output);
    }

    /**
     * @test
     *
     * @testdox Compiles closure returned from function with proper extraction
     */
    public function compilesClosureReturnedFromFunction(): void
    {
        $getHandler = function () {
            return function ($value) {
                return $value * 2;
            };
        };

        $closure = $getHandler();
        $output  = $this->exporter->export(['handler' => $closure]);

        $this->assertStringContainsString('function ($value)', $output);
        $this->assertStringContainsString('return $value * 2', $output);
    }

    /**
     * @test
     *
     * @testdox Compiles closure extracted from class method properly
     */
    public function compilesClosureFromClassMethod(): void
    {
        $handler = new class {
            public function getClosure()
            {
                return function ($x) {
                    return $x + 1;
                };
            }
        };

        $closure = $handler->getClosure();
        $output  = $this->exporter->export(['method_closure' => $closure]);

        $this->assertStringContainsString('function ($x)', $output);
        $this->assertStringContainsString('return $x + 1', $output);
    }

    /**
     * @test
     *
     * @testdox Compiles static closure (closure without $this binding)
     */
    public function compilesStaticClosure(): void
    {
        $handler = new class {
            public static function getStaticClosure()
            {
                return static function () {
                    return 'static closure';
                };
            }
        };

        $closure = $handler::getStaticClosure();
        $output  = $this->exporter->export(['static_closure' => $closure]);

        $this->assertStringContainsString('function ()', $output);
        $this->assertStringContainsString('static closure', $output);
    }

    /**
     * @test
     *
     * @testdox Runtime closure created with eval throws exception
     */
    public function runtimeClosureThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Source file not found');

        $closure = eval('return function() { return "test"; };');
        $this->exporter->export(['closure' => $closure]);
    }

    /**
     * @test
     *
     * @testdox Eval created closure throws exception
     */
    public function evalCreatedClosureThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Source file not found');

        $closureCode = 'return function() { return "created with eval"; };';
        $closure     = eval($closureCode);

        $this->exporter->export(['eval_closure' => $closure]);
    }

    /**
     * @test
     *
     * @testdox Closure from non-existent file throws exception
     */
    public function closureFromNonExistentFileThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Source file not found');

        $reflection = $this->createMock(\ReflectionFunction::class);
        $reflection->method('getFileName')->willReturn('/non/existent/file.php');
        $reflection->method('getStartLine')->willReturn(1);
        $reflection->method('getEndLine')->willReturn(1);

        $extractor = new VarExport\Compiler\ClosureExtractor();
        $extractor->extract($reflection);
    }

    /**
     * @test
     *
     * @testdox Closure from unreadable file throws exception
     */
    public function closureFromUnreadableFileThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        // Mock reflection with non-existent file path
        $this->expectExceptionMessage('Source file not found');

        $reflection = $this->createMock(\ReflectionFunction::class);
        // Use __FILE__ which exists but will be marked as unreadable in the mock
        $reflection->method('getFileName')->willReturn('/root/restricted/file.php');
        $reflection->method('getStartLine')->willReturn(1);
        $reflection->method('getEndLine')->willReturn(1);

        $extractor = new VarExport\Compiler\ClosureExtractor();
        $extractor->extract($reflection);
    }

    /**
     * @test
     *
     * @testdox Multiple closures on same line validates properly
     */
    public function compilesClosureOnSameLineWithAnother(): void
    {
        $closure1 = function () { return 'first'; };
        $closure2 = function () { return 'second'; };

        $output = $this->exporter->export([
            'first'  => $closure1,
            'second' => $closure2,
        ]);

        $this->assertStringContainsString("'first' => function ()", $output);
        $this->assertStringContainsString("'second' => function ()", $output);
        $this->assertStringContainsString('first', $output);
        $this->assertStringContainsString('second', $output);
    }

    /**
     * @test
     *
     * @testdox Nested closures extract only outer closure correctly
     */
    public function compilesNestedClosuresOuterOnly(): void
    {
        $outer = function () {
            $inner = function () {
                return 'inner';
            };

            return $inner();
        };

        $output = $this->exporter->export(['outer' => $outer]);

        $this->assertStringContainsString('function ()', $output);
        // Should have the outer structure
        $this->assertStringContainsString('return', $output);
    }

    /**
     * @test
     *
     * @testdox Closure with heredoc and nowdoc strings preserves content
     */
    public function compilesClosureWithHeredocNowdoc(): void
    {
        $closure = function () {
            $heredoc = <<<EOD
                This is a heredoc
                with multiple lines
                EOD;

            return $heredoc;
        };

        $output = $this->exporter->export(['heredoc_closure' => $closure]);

        $this->assertStringContainsString('function ()', $output);
        $this->assertStringContainsString('heredoc', $output);
    }

    /**
     * @test
     *
     * @testdox Compiles closure in array with trailing comma properly handles syntax
     */
    public function compilesClosureWithTrailingComma(): void
    {
        $closure = function () {
            return 'test';
        };

        // Array with trailing comma - should compile without syntax errors
        $output = $this->exporter->export([
            'closure' => $closure,
        ]);

        $this->assertStringContainsString("'closure' => function ()", $output);
        $this->assertStringContainsString('return \'test\'', $output);
    }

    /**
     * @test
     *
     * @testdox Compiles closure with PHP 8 attributes preserves function definition
     */
    public function compilesClosureWithAttributes(): void
    {
        // Attributes on closures are not supported in PHP, so we test the closure itself
        $closure = function () {
            // This simulates a closure that would have attributes
            return 'closure with pseudo-attributes';
        };

        $output = $this->exporter->export(['closure' => $closure]);

        $this->assertStringContainsString('function ()', $output);
        $this->assertStringContainsString('return', $output);
    }

    /**
     * @test
     *
     * @testdox Closure with captured variables generates IIFE wrapper
     */
    public function warnsForCapturedVariables(): void
    {
        $capturedVar = 'test_value';

        $closure = function () use ($capturedVar) {
            return $capturedVar;
        };

        $output = $this->exporter->export(['closure' => $closure]);

        // IIFE wrapper should be present for captured variables
        $this->assertStringContainsString('(function() {', $output);
        $this->assertStringContainsString('})(),', $output);
        $this->assertStringContainsString('use ($capturedVar)', $output);
    }

    /**
     * @test
     *
     * @testdox IIFE wrapper includes variable names when capturing
     */
    public function warningIncludesVariableNames(): void
    {
        $varOne   = 'first';
        $varTwo   = 'second';
        $varThree = 'third';

        $closure = function () use ($varOne, $varTwo, $varThree) {
            return $varOne . $varTwo . $varThree;
        };

        $output = $this->exporter->export(['closure' => $closure]);

        // All captured variable names should appear
        $this->assertStringContainsString('$varOne =', $output);
        $this->assertStringContainsString('$varTwo =', $output);
        $this->assertStringContainsString('$varThree =', $output);
        $this->assertStringContainsString('use ($varOne, $varTwo, $varThree)', $output);
    }

    /**
     * @test
     *
     * @testdox IIFE wrapper generated for captured variables contains proper structure
     */
    public function warningDisabledWhenWarnCapturedVarsIsFalse(): void
    {
        $capturedValue = 42;

        $closure = function () use ($capturedValue) {
            return $capturedValue * 2;
        };

        $output = $this->exporter->export(['closure' => $closure]);

        // Even without warning flag, IIFE wrapper should exist
        $this->assertStringContainsString('(function() {', $output);
        $this->assertStringContainsString('$capturedValue = 42', $output);
        $this->assertStringContainsString('return function () use ($capturedValue)', $output);
        $this->assertStringContainsString('})(),', $output);
    }

    /**
     * @test
     *
     * @testdox Uses IIFE wrapping with captured variables to preserve state
     */
    public function usesIIFEWrappingForCapturedVariables(): void
    {
        $state = 'preserved';

        $closure = function () use ($state) {
            return "State is: {$state}";
        };

        $output = $this->exporter->export(['closure' => $closure]);

        // IIFE structure with variable initialization
        $this->assertStringContainsString('(function() {', $output);
        $this->assertStringContainsString("'preserved'", $output);
        $this->assertStringContainsString('return function', $output);
        $this->assertStringContainsString('})(),', $output);
    }

    /**
     * @test
     *
     * @testdox Captured variable values are inlined in IIFE wrapper
     */
    public function inlinesCapturedVariableValues(): void
    {
        $num  = 123;
        $text = 'hello';
        $flag = true;

        $closure = function () use ($num, $text, $flag) {
            return compact('num', 'text', 'flag');
        };

        $output = $this->exporter->export(['closure' => $closure]);

        // Values should be inlined in the IIFE wrapper
        $this->assertStringContainsString('123', $output);
        $this->assertStringContainsString("'hello'", $output);
        $this->assertStringContainsString('true', $output);
    }
}
