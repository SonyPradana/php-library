<?php

namespace System\Console\Interfaces;

interface BoxSize
{
    public function width(): int;

    public function height(): int;

    /**
     * @return int[] order: top, right, bottom, left
     */
    public function margin(): array;

    /**
     * @return int[] order: top, right, bottom, left
     */
    public function padding(): array;

    public function border(): Border;
}
