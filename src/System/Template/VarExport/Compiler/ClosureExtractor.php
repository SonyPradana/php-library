<?php

declare(strict_types=1);

namespace System\Template\VarExport\Compiler;

/**
 * ClosureExtractor - Extract and normalize closure source code using tokenization.
 *
 * This class handles the complex task of extracting closure definitions from source files
 * using PHP's tokenizer to accurately parse closure boundaries without regex.
 *
 * Features:
 * - Token-based parsing
 * - Handles function() and fn() syntax
 * - Tracks brace/parenthesis depth
 * - Normalizes indentation
 * - Returns structured data
 */
final class ClosureExtractor
{
    /**
     * Extract closure from ReflectionFunction.
     *
     * @return array{
     *   lines: string[],
     *   original: string,
     *   normalized: string,
     *   metadata: array{
     *     startLine: int,
     *     endLine: int,
     *     file: string,
     *     isSingleLine: bool,
     *     isArrowFunction: bool,
     *     minIndent: int
     *   }
     * }
     */
    public function extract(\ReflectionFunction $reflection): array
    {
        $this->validateReflection($reflection);

        $file         = $reflection->getFileName();
        $startLine    = $reflection->getStartLine();
        $endLine      = $reflection->getEndLine();
        $isSingleLine = $startLine === $endLine;

        // Read source lines
        $sourceLines  = $this->readSourceLines($file, $startLine, $endLine);
        $originalCode = implode('', $sourceLines);

        // Extract pure closure code (without prefix/suffix)
        if ($isSingleLine) {
            $closureCode = $this->extractFromSingleLine($originalCode, $file, $startLine);
        } else {
            $closureCode = $originalCode;
        }

        // Parse into structured data
        $tokens = $this->tokenize($closureCode);
        $ast    = $this->buildClosureAST($tokens);

        // Normalize closure code
        $closureCode     = $this->normalizeClosureCode($closureCode);
        $lines           = explode("\n", $closureCode);
        $minIndent       = $this->findMinimumIndentation($lines);
        $normalizedLines = $this->removeIndentation($lines, $minIndent);
        $normalizedCode  = implode("\n", $normalizedLines);

        return [
            'lines'      => $normalizedLines,
            'original'   => $originalCode,
            'normalized' => $normalizedCode,
            'metadata'   => [
                'startLine'       => $startLine,
                'endLine'         => $endLine,
                'file'            => $file,
                'isSingleLine'    => $isSingleLine,
                'isArrowFunction' => $ast['isArrowFunction'],
                'minIndent'       => $minIndent,
            ],
            'ast' => $ast,
        ];
    }

    /**
     * Validate that reflection is compilable.
     */
    private function validateReflection(\ReflectionFunction $reflection): void
    {
        $file = $reflection->getFileName();

        if (false === $file) {
            throw new \InvalidArgumentException('Cannot extract runtime-created closure. Closure must be defined in a source file.');
        }

        if (false === file_exists($file)) {
            throw new \InvalidArgumentException("Source file not found: {$file}");
        }

        if (false === is_readable($file)) {
            throw new \InvalidArgumentException("Source file not readable: {$file}");
        }
    }

    /**
     * Read source lines from file.
     *
     * @return string[]
     */
    private function readSourceLines(string $file, int $startLine, int $endLine): array
    {
        $lines = file($file);

        if (false === $lines) {
            throw new \InvalidArgumentException("Cannot read file: {$file}");
        }

        return array_slice($lines, $startLine - 1, $endLine - $startLine + 1);
    }

    /**
     * Extract closure from single line using tokenization.
     */
    private function extractFromSingleLine(string $line, string $file, int $lineNumber): string
    {
        // Tokenize the line
        $tokens = $this->tokenize('<?php ' . $line);

        // Find closure boundaries
        $closureStart    = null;
        $closureEnd      = null;
        $inClosure       = false;
        $braceDepth      = 0;
        $parenDepth      = 0;
        $isArrowFunction = false;

        for ($i = 0; $i < count($tokens); $i++) {
            $token = $tokens[$i];

            // Skip PHP open tag
            if ($this->isToken($token, T_OPEN_TAG)) {
                continue;
            }

            // Detect closure start
            if (false === $inClosure && ($this->isToken($token, T_FUNCTION) || $this->isToken($token, T_FN))) {
                $inClosure       = true;
                $closureStart    = $i;
                $isArrowFunction = $this->isToken($token, T_FN);
                continue;
            }

            if (false === $inClosure) {
                continue;
            }

            // Track parentheses (parameters)
            if ($this->isChar($token, '(')) {
                $parenDepth++;
            } elseif ($this->isChar($token, ')')) {
                $parenDepth--;
            }

            // Track braces (body)
            if ($this->isChar($token, '{')) {
                $braceDepth++;
            } elseif ($this->isChar($token, '}')) {
                $braceDepth--;

                // End of function closure
                if ($braceDepth === 0 && !$isArrowFunction) {
                    $closureEnd = $i;
                    break;
                }
            }

            // Handle arrow function
            if ($isArrowFunction && $this->isToken($token, T_DOUBLE_ARROW)) {
                // Continue until we hit delimiter
                for ($j = $i + 1; $j < count($tokens); $j++) {
                    $nextToken = $tokens[$j];

                    if ($this->isChar($nextToken, ',')
                        || $this->isChar($nextToken, ';')
                        || $this->isChar($nextToken, ')')) {
                        $closureEnd = $j - 1;
                        break 2;
                    }
                }
            }
        }

        if (null === $closureStart || null === $closureEnd) {
            throw new \InvalidArgumentException("Could not extract closure from line {$lineNumber} in {$file}");
        }

        // Extract tokens between start and end
        $closureTokens = array_slice($tokens, $closureStart, $closureEnd - $closureStart + 1);

        // Reconstruct code from tokens
        return $this->tokensToString($closureTokens);
    }

    /**
     * Tokenize PHP code.
     *
     * @return array<int, array{0: int, 1: string, 2: int}|string>
     */
    private function tokenize(string $code): array
    {
        return token_get_all($code);
    }

    /**
     * Build AST-like structure from tokens.
     *
     * @param array<int, array<int, int|string>|string> $tokens
     *
     * @return array{
     *   type: string,
     *   isArrowFunction: bool,
     *   parameters: string[],
     *   body: string,
     *   returnType: string|null
     * }
     */
    private function buildClosureAST(array $tokens): array
    {
        $ast = [
            'type'            => 'closure',
            'isArrowFunction' => false,
            'parameters'      => [],
            'body'            => '',
            'returnType'      => null,
        ];

        $state  = 'initial';
        $buffer = '';

        foreach ($tokens as $token) {
            if ($this->isToken($token, T_OPEN_TAG)) {
                continue;
            }

            // Detect type
            if ('initial' === $state) {
                if ($this->isToken($token, T_FUNCTION)) {
                    $ast['isArrowFunction'] = false;
                    $state                  = 'parameters';
                    continue;
                } elseif ($this->isToken($token, T_FN)) {
                    $ast['isArrowFunction'] = true;
                    $state                  = 'parameters';
                    continue;
                }
            }

            // Parse parameters (simplified - can be enhanced)
            if ('parameters' === $state && $this->isChar($token, '(')) {
                $state = 'inside_params';
                continue;
            }

            if ('inside_params' === $state) {
                if ($this->isChar($token, ')')) {
                    $ast['parameters'][] = trim($buffer);
                    $buffer              = '';
                    $state               = 'after_params';
                    continue;
                }

                $buffer .= $this->tokenToString($token);
            }

            // Detect use clause
            if ('after_params' === $state) {
                if ($this->isToken($token, T_USE)) {
                    $state = 'use_clause';
                    continue;
                }

                if ($this->isChar($token, ':')) {
                    $state = 'return_type';
                    continue;
                }

                if ($this->isToken($token, T_DOUBLE_ARROW) || $this->isChar($token, '{')) {
                    $state  = 'body';
                    $buffer = $this->tokenToString($token);
                    continue;
                }
            }

            // Parse return type
            if ('return_type' === $state) {
                if ($this->isChar($token, '{') || $this->isToken($token, T_DOUBLE_ARROW)) {
                    $ast['returnType'] = trim($buffer);
                    $buffer            = $this->tokenToString($token);
                    $state             = 'body';
                    continue;
                }

                $buffer .= $this->tokenToString($token);
            }

            // Parse body
            if ('body' === $state) {
                $buffer .= $this->tokenToString($token);
            }
        }

        $ast['body'] = trim($buffer);

        return $ast;
    }

    /**
     * Convert tokens back to string.
     *
     * @param array<int, array<int, int|string>|string> $tokens
     */
    private function tokensToString(array $tokens): string
    {
        $code = '';

        foreach ($tokens as $token) {
            $code .= $this->tokenToString($token);
        }

        return $code;
    }

    /**
     * Convert single token to string.
     *
     * @param array{0: int, 1: string, 2: int}|string $token
     */
    private function tokenToString(array|string $token): string
    {
        return is_array($token) ? $token[1] : $token;
    }

    /**
     * Check if token is specific type.
     *
     * @param array<int, int|string>|string $token
     */
    private function isToken(array|string $token, int $type): bool
    {
        return is_array($token) && $token[0] === $type;
    }

    /**
     * Check if token is specific character.
     *
     * @param array<int, int|string>|string $token
     */
    private function isChar(array|string $token, string $char): bool
    {
        return is_string($token) && $token === $char;
    }

    private function normalizeClosureCode(string $closureCode): string
    {
        // Remove trailing array delimiter comma on last non-empty line
        $lines = explode("\n", $closureCode);
        for ($i = count($lines) - 1; $i >= 0; $i--) {
            if (trim($lines[$i]) === '') {
                continue;
            }

            $trimmed = rtrim($lines[$i]);
            if ('' !== $trimmed && (substr($trimmed, -1) === ',')) {
                $lines[$i] = rtrim(substr($trimmed, 0, -1));
            }

            break;
        }

        // Remove trailing empty lines after trimming comma so we don't leave
        // a blank line between the closure and the array-level comma.
        while (false === empty($lines) && '' === trim($lines[count($lines) - 1])) {
            array_pop($lines);
        }

        return implode("\n", $lines);
    }


    /**
     * Find minimum indentation in lines.
     *
     * @param string[] $lines
     */
    private function findMinimumIndentation(array $lines): int
    {
        $minIndent = PHP_INT_MAX;

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }

            if (preg_match('/^(\s*)/', $line, $matches)) {
                $minIndent = min($minIndent, strlen($matches[1]));
            }
        }

        return PHP_INT_MAX === $minIndent ? 0 : $minIndent;
    }

    /**
     * Remove indentation from lines.
     *
     * @param string[] $lines
     *
     * @return string[]
     */
    private function removeIndentation(array $lines, int $indent): array
    {
        if (0 === $indent) {
            return $lines;
        }

        $normalized = [];

        foreach ($lines as $line) {
            if ('' === trim($line)) {
                $normalized[] = '';
                continue;
            }

            $normalized[] = substr($line, min($indent, strlen($line)));
        }

        return $normalized;
    }

    /**
     * Validate single line doesn't have multiple closures.
     */
    public function validateSingleLine(string $line, int $lineNumber): void
    {
        $tokens = $this->tokenize('<?php ' . $line);

        $closureCount = 0;

        foreach ($tokens as $token) {
            if ($this->isToken($token, T_FUNCTION) || $this->isToken($token, T_FN)) {
                $closureCount++;
            }
        }

        if ($closureCount > 1) {
            throw new \InvalidArgumentException("Multiple closures detected on line {$lineNumber}. Each closure must be on separate line(s).\n" . 'Line: ' . trim($line));
        }
    }
}
