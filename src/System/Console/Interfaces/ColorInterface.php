<?php

namespace System\Console\Interfaces;

interface ColorInterface
{
    public function get(): array;

    public function raw(): string;
}
