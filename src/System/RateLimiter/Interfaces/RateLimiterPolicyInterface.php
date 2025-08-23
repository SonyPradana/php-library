<?php

declare(strict_types=1);

namespace System\RateLimiter\Interfaces;

use System\RateLimiter\RateLimit;

interface RateLimiterPolicyInterface
{
    public function consume(string $key, int $token = 1): RateLimit;

    public function peek(string $key): RateLimit;

    public function reset(string $key): void;
}
