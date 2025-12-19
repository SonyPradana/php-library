<?php

declare(strict_types=1);

namespace System\Template;

final class VarExport
{
    /** @var string[] */
    private array $buffer    = [];
    private string $indent   = '    ';
    private int $indentLevel = 0;
    private bool $alignArray = false;

    public function setIndetation(string $indent): self
    {
        $this->indent = $indent;

        return $this;
    }

    public function setIndetationLevel(int $indentLevel): self
    {
        $this->indentLevel = $indentLevel;

        return $this;
    }

    public function setAlignArray(bool $align = true): self
    {
        $this->alignArray = $align;

        return $this;
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public function compile(array $data, string $file_name): bool
    {
        return file_put_contents($file_name, $this->compileToString($data));
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public function export(array $data): string
    {
        $this->compileValue($data);

        return $this->getBuffer();
    }

    /**
     * @param array<array-key, mixed> $data
     */
    private function compileToString(array $data): string
    {
        $this->addToBuffer('<?php');
        $this->addLine(2);
        $this->addToBuffer('declare(strict_types=1);');
        $this->addLine(2);
        $this->addToBuffer('// auto-generated file, do not edit!');
        $this->addLine();
        $this->addToBuffer('// generated on ' . date('Y-m-d H:i:s'));
        $this->addLine(2);
        $this->addToBuffer('return ');
        $this->compileValue($data); // compiles and adds to buffer
        $this->addToBuffer(';');
        $this->addLine();

        return $this->getBuffer();
    }

    private function compileValue(mixed $value): void
    {
        match (true) {
            is_array($value)           => $this->compileArray($value),
            $value instanceof \Closure => $this->compileClosure($value),
            is_string($value)          => $this->compileString($value),
            is_int($value)             => $this->compileInteger($value),
            is_bool($value)            => $this->compileBoolean($value),
            is_float($value)           => $this->compileFloat($value),
            is_null($value)            => $this->compileNull(),
            is_object($value)          => throw new \InvalidArgumentException('Cannot compile resource type'),
            is_resource($value)        => throw new \InvalidArgumentException('Cannot compile resource type'),
            default                    => $this->compileFallback($value),
        };
    }

    /**
     * @param array<array-key, mixed> $array
     *
     * @internal
     */
    private function compileArray(array $array): void
    {
        if (empty($array)) {
            $this->addToBuffer('[]');

            return;
        }

        $this->openArray();

        // count longst array key length
        $keyLength = 0;
        foreach (array_keys($array) as $key) {
            $keyLength = max($keyLength, strlen((string) $key));
        }

        foreach ($array as $key => $value) {
            $this->writeArrayElement($key, $value, $keyLength);
        }

        $this->closeArray();
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function compileClosure(\Closure $closure): void
    {
        $reflection = $this->reflectClosure($closure);

        $this->validateClosure($reflection);

        $capturedVars = $reflection->getStaticVariables();

        [] === $capturedVars
            ? $this->handleClosure($reflection)
            : $this->handleClosureVars($reflection, $capturedVars);
    }

    /**
     * @internal
     */
    private function compileString(string $string): void
    {
        $this->addToBuffer("'" . addslashes($string) . "'");
    }

    /**
     * @internal
     */
    private function compileInteger(int $int): void
    {
        $this->addToBuffer((string) $int);
    }

    /**
     * @internal
     */
    private function compileBoolean(bool $bool): void
    {
        $this->addToBuffer($bool ? 'true' : 'false');
    }

    /**
     * @internal
     */
    private function compileFloat(float $float): void
    {
        $formatted_float = $float == round($float)
           ? number_format($float, 1, '.', '')
           : (string) $float;

        $this->addToBuffer($formatted_float);
    }

    /**
     * @internal
     */
    private function compileNull(): void
    {
        $this->addToBuffer('null');
    }

    /**
     * @internal
     */
    private function compileFallback(mixed $value): void
    {
        $this->addToBuffer(var_export($value, true));
    }

    public function flush(): void
    {
        $this->buffer      = [];
        $this->indentLevel = 0;
    }

    public function getBuffer(): string
    {
        $content = implode('', $this->buffer);
        $this->flush();

        return $content;
    }

    private function getLastBuffer(): ?string
    {
        $last = array_key_last($this->buffer);

        return $this->buffer[$last] ?? null;
    }

    // private

    private function openArray(): void
    {
        $this->addToBuffer('[');
        $this->addLine();
        $this->indentLevel++;
    }

    private function closeArray(): void
    {
        $this->indentLevel--;
        $this->addIndentation();
        $this->addToBuffer(']');
    }

    private function writeArrayKey(int|string $key): void
    {
        if (is_string($key)) {
            $this->compileString($key);
        } else {
            $this->addToBuffer((string) $key);
        }
    }

    /**
     * @param array<array-key, mixed> $array
     */
    private function isAssociativeArray(array $array): bool
    {
        if (empty($array)) {
            return false;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }

    private function writeArrayElement(int|string $key, mixed $value, int $keyLength): void
    {
        $this->addIndentation();
        $this->writeArrayKey($key);

        // count key aligment
        if (true === $this->alignArray) {
            $buffer = $this->getLastBuffer() ?? $key;
            $lenght = strlen((string) $buffer);
            $this->addToBuffer(str_repeat(' ', max(0, ($keyLength - $lenght) + $this->indentLevel)));
        }

        $this->addToBuffer(' => ');
        $this->compileValue($value);
        $this->addToBuffer(',');
        $this->addLine();
    }

    private function reflectClosure(\Closure $closure): \ReflectionFunction
    {
        try {
            return new \ReflectionFunction($closure);
        } catch (\ReflectionException $e) {
            throw new \InvalidArgumentException("Failed to reflect closure: {$e->getMessage()}");
        }
    }

    private function validateClosure(\ReflectionFunction $reflection): void
    {
        $file = $reflection->getFileName();

        if (false === $file) {
            throw new \InvalidArgumentException('Cannot compile runtime-created closure (eval, create_function, etc.)');
        }

        if (false === file_exists($file)) {
            throw new \InvalidArgumentException("Closure source file not found: {$file}");
        }

        if (false === is_readable($file)) {
            throw new \InvalidArgumentException("Closure source file not readable: {$file}");
        }
    }

    /**
     * @param array<string, mixed> $capturedVars
     */
    private function handleClosureVars(\ReflectionFunction $reflection, array $capturedVars): void
    {
        $sourceCode = $this->extractClosureSource($reflection);
        $lineOfCode = $this->normalizeClosureIndentation($sourceCode);

        $this->wrapClosure($lineOfCode, $capturedVars);
    }

    private function handleClosure(\ReflectionFunction $reflection): void
    {
        $sourceCode = $this->extractClosureSource($reflection);
        $lineOfCode = $this->normalizeClosureIndentation($sourceCode);

        foreach ($lineOfCode as $line) {
            $this->addToBuffer($line);
        }
    }

    /**
     * @return string[]
     */
    private function extractClosureSource(\ReflectionFunction $reflection): array
    {
        $file      = $reflection->getFileName();
        $startLine = $reflection->getStartLine();
        $endLine   = $reflection->getEndLine();

        $lines = file($file);
        if (false === $lines) {
            throw new \InvalidArgumentException("Cannot read file: {$file}");
        }

        return array_slice(
            $lines,
            $startLine - 1,
            $endLine - $startLine + 1
        );
    }

    /**
     * @param string[] $linesOfCode
     *
     * @return string[]
     */
    private function normalizeClosureIndentation(array $linesOfCode): array
    {
        $minIndent = $this->findMinimumIndentation($linesOfCode);

        return $minIndent === PHP_INT_MAX
             ? $linesOfCode
             : $this->removeIndentation($linesOfCode, $minIndent);
    }

    /**
     * @param string[] $linesOfCode
     */
    private function findMinimumIndentation(array $linesOfCode): int
    {
        $minIndent = PHP_INT_MAX;

        foreach ($linesOfCode as $line) {
            if (trim($line) === '') {
                continue;
            }

            if (preg_match('/^(\s*)/', $line, $matches)) {
                $minIndent = min($minIndent, strlen($matches[1]));
            }
        }

        return $minIndent;
    }

    /**
     * @param string[] $lines
     *
     * @return string[]
     */
    private function removeIndentation(array $lines, int $minIndent): array
    {
        $normalized = [];

        foreach ($lines as $line) {
            if (trim($line) === '') {
                $normalized[] = '';
            } else {
                $normalized[] = substr($line, min($minIndent, strlen($line)));
            }
        }

        return $normalized;
    }

    /**
     * @param string[]             $closureCode
     * @param array<string, mixed> $capturedVars
     */
    private function wrapClosure(array $closureCode, array $capturedVars): void
    {
        $this->addToBuffer('(function() {');
        $this->addLine();
        $this->indentLevel++;

        foreach ($capturedVars as $name => $value) {
            $this->addIndentation();
            $this->addToBuffer('$' . $name . ' = ');
            $this->compileValue($value);
            $this->addToBuffer(';');
            $this->addLine();
        }

        $this->addIndentation();
        $this->addToBuffer('return ' . implode('', $closureCode) . ';');
        $this->addLine();

        $this->indentLevel--;
        $this->addIndentation();
        $this->addToBuffer('})()');
    }

    // buffer helpers

    private function addToBuffer(string $content): string
    {
        return $this->buffer[] = $content;
    }

    private function addLine(int $repeat = 1): string
    {
        return $this->addToBuffer(str_repeat(PHP_EOL, $repeat));
    }

    private function addIndentation(): void
    {
        if ($this->indentLevel > 0) {
            $fullIndent = str_repeat($this->indent, $this->indentLevel);
            $this->addToBuffer($fullIndent);
        }
    }
}
