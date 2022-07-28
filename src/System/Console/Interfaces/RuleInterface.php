<?php

namespace System\Console\Interfaces;

interface RuleInterface
{
    public function get(): array;

    public function raw(): string;
}
