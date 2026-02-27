<?php

declare(strict_types=1);

namespace System\Template\VarExport\Value;

/**
 * Represents a PHP constant that should be exported by its name rather than its value.
 */
final class Constant
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
