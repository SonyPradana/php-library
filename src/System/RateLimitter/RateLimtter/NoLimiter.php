<?php

declare(strict_types=1);

namespace System\RateLimitter\NoLimiter;

use System\RateLimitter\Interfaces\RateLimiterPolicyInterface;
use System\RateLimitter\RateLimit;

class NoLimiter implements RateLimiterPolicyInterface
{
    public function consume(string $key, int $token = 1): RateLimit
    {
        return new RateLimit($key, 0, 0, 0, false);
    }

    public function peek(string $key): RateLimit
    {
        return new RateLimit($key, 0, 0, 0, false);
    }

    public function reset(string $key): void
    {
        // No operation for NoLimiter
    }
}
