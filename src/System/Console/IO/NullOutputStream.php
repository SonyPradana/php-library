<?php

declare(strict_types=1);

namespace System\Console\IO;

use System\Console\Interfaces\OutputStream;

class NullOutputStream implements OutputStream
{
    public function write(string $message): void
    {
    }

    public function isInteractive(): bool
    {
        return false;
    }
}
