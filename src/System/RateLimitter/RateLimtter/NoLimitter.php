<?php

declare(strict_types=1);

namespace System\RateLimitter\RateLimtter;

use System\RateLimitter\Interfaces\RateLimiterPolicyInterface;
use System\RateLimitter\RateLimit;

class NoLimitter implements RateLimiterPolicyInterface
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
