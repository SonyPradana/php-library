<?php

declare(strict_types=1);

namespace System\RateLimitter;

use System\RateLimitter\Interfaces\RateLimiterInterface;
use System\RateLimitter\Interfaces\RateLimiterPolicyInterface;

class RateLimiter implements RateLimiterInterface
{
    public function __construct(private RateLimiterPolicyInterface $limitter)
    {
    }

    public function isBlocked(string $key, int $maxAttempts, int|\DateInterval $decay): bool
    {
        return $this->limitter->peek($key)->isBlocked();
    }

    public function consume(string $key, int $decayMinutes = 1): int
    {
        return $this->limitter->consume($key, 1)->getConsumed();
    }

    public function getCount(string $key): int
    {
        return $this->limitter->peek($key)->getConsumed();
    }

    public function getRetryAfter(string $key): int
    {
        $result = $this->limitter->peek($key);
        if (null === $result->getRetryAfter()) {
            return 0;
        }

        return max(0, $result->getRetryAfter()->getTimestamp() - now()->timestamp);
    }

    public function remaining(string $key, int $maxAttempts): int
    {
        return $this->limitter->peek($key)->getRemaining();
    }

    public function reset(string $key): void
    {
        $this->limitter->reset($key);
    }
}
