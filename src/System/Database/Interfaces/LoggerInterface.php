<?php

declare(strict_types=1);

namespace System\Database\Interfaces;

interface LoggerInterface
{
    /**
     * Flush logs query.
     */
    public function flushLogs(): void;

    /**
     * Get logs query.
     *
     * @return array<int, array<string, float|string|null>> The return of started, ended and duration in milisocond
     */
    public function getLogs(): array;
}
