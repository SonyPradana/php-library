<?php

declare(strict_types=1);

namespace System\RateLimiter\RateLimiter;

use System\RateLimiter\Interfaces\RateLimiterPolicyInterface;
use System\RateLimiter\RateLimit;

class NoLimiter implements RateLimiterPolicyInterface
{
    public function consume(string $key, int $token = 1): RateLimit
    {
        return new RateLimit(
            identifier: $key,
            limit: PHP_INT_MAX,
            consumed: 0,
            remaining: PHP_INT_MAX,
            isBlocked: false
        );
    }

    public function peek(string $key): RateLimit
    {
        return new RateLimit(
            identifier: $key,
            limit: PHP_INT_MAX,
            consumed: 0,
            remaining: PHP_INT_MAX,
            isBlocked: false
        );
    }

    public function reset(string $key): void
    {
        // No operation for NoLimiter
    }
}
