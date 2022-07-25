<?php

declare(strict_types=1);

namespace System\Console\Style\Color;

use System\Console\Interfaces\RuleInterface;

abstract class Color implements RuleInterface
{
    protected $rule;

    public function __construct($rule)
    {
        $this->rule = $rule;
    }

    public function get(): array
    {
        return $this->rule;
    }

    public function raw(): string
    {
        return implode(';', $this->rule);
    }
}
