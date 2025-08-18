<?php

declare(strict_types=1);

namespace System\RateLimitter\Interfaces;

use System\RateLimitter\RateLimit;

interface RateLimiterPolicyInterface
{
    public function consume(string $key, int $token = 1): RateLimit;

    public function peek(string $key): RateLimit;

    public function reset(string $key): void;
}
