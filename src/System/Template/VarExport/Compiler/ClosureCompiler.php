<?php

declare(strict_types=1);

namespace System\Template\VarExport\Compiler;

class ClosureCompiler extends Compiler
{
    private \ReflectionFunction $reflection;

    public function getReflection(): \ReflectionFunction
    {
        return $this->reflection;
    }

    public function compile(mixed $data): array
    {
        $this->reflection = $this->reflectClosure($data);

        $this->validateClosure($this->reflection);

        return $this->handleClosure($this->reflection);
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
     * @return string[]
     */
    private function handleClosure(\ReflectionFunction $reflection): array
    {
        return $this->normalizeClosureIndentation(
            linesOfCode: $this->extractClosureSource($reflection)
        );
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
}
