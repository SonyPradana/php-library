<?php

declare(strict_types=1);

namespace System\RateLimitter\Interfaces;

interface RateLimiterInterface
{
    public function isBlocked(string $key, int $maxAttempts, int|\DateInterval $decay): bool;

    public function consume(string $key, int $decayMinutes = 1): int;

    public function getCount(string $key): int;

    public function getRetryAfter(string $key): int;

    public function remaining(string $key, int $maxAttempts): int;

    public function reset(string $key): void;
}
