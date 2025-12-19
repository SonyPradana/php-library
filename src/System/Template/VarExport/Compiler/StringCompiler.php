<?php

declare(strict_types=1);

namespace System\Template\VarExport\Compiler;

class StringCompiler extends Compiler
{
    public function compile(mixed $data): array
    {
        return ["'" . addslashes($data) . "'"];
    }
}
