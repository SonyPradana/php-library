<?php

declare(strict_types=1);

namespace System\Template\VarExport\Compiler;

abstract class Compiler
{
    /**
     * @return string[]
     */
    abstract public function compile(mixed $data): array;
}
