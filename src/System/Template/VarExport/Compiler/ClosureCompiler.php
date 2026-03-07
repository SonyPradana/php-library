<?php

declare(strict_types=1);

namespace System\Template\VarExport\Compiler;

use System\Template\VarExport\ClosureExtractor;

class ClosureCompiler extends Compiler
{
    private \ReflectionFunction $reflection;
    private ?ClosureExtractor $extractor;

    public function __construct()
    {
        $this->extractor = new ClosureExtractor();
    }

    public function getReflection(): \ReflectionFunction
    {
        return $this->reflection;
    }

    public function compile(mixed $data): array
    {
        $this->reflection = $this->reflectClosure($data);

        return $this
            ->extractor
            ->extract($this->reflection)['lines'];
    }

    public function reflectClosure(\Closure $closure): \ReflectionFunction
    {
        try {
            return new \ReflectionFunction($closure);
        } catch (\ReflectionException $e) {
            throw new \InvalidArgumentException("Failed to reflect closure: {$e->getMessage()}");
        }
    }
}
