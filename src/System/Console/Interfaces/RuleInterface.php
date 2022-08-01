<?php

namespace System\Console\Interfaces;

interface RuleInterface
{
    /**
     * @return array<int, int>
     */
    public function get(): array;

    public function raw(): string;
}
