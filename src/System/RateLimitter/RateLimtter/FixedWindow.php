<?php

declare(strict_types=1);

namespace System\RateLimitter\NoLimiter;

use System\Cache\CacheInterface;
use System\RateLimitter\Interfaces\RateLimiterPolicyInterface;
use System\RateLimitter\RateLimit;

class FixedWindow implements RateLimiterPolicyInterface
{
    public function __construct(
        private CacheInterface $cache,
        private int $limit,
        private int $windowsMinutes,
    ) {
    }

    public function consume(string $key, int $token = 1): RateLimit
    {
        $windowKey = $this->getWindowKey($key);
        $consumed  = (int) $this->cache->get($windowKey, 0);

        if ($consumed + $token > $this->limit) {
            return new RateLimit(
                identifier: $key,
                limit: $this->limit,
                consumed: $consumed,
                remaining: max(0, $this->limit - $consumed),
                isBlocked: true,
                retryAfter: $this->getNextWindowStart(),
            );
        }

        $newConsumed = $this->cache->increment($windowKey, 1);
        if (1 === $newConsumed) {
            $this->cache->set($windowKey, 1, $this->windowsMinutes);
        }

        return new RateLimit(
            identifier: $key,
            limit: $this->limit,
            consumed: $newConsumed,
            remaining: $this->limit - $newConsumed,
            isBlocked: false,
            retryAfter: $this->getNextWindowStart(),
        );
    }

    public function peek(string $key): RateLimit
    {
        $windowKey = $this->getWindowKey($key);
        $consumed  = (int) $this->cache->get($windowKey, 0);

        return new RateLimit(
            identifier: $key,
            limit: $this->limit,
            consumed: $consumed,
            remaining: max(0, $this->limit - $consumed),
            isBlocked: $consumed < $this->limit,
            retryAfter: $this->getNextWindowStart(),
        );
    }

    public function reset(string $key): void
    {
        $this->cache->delete($this->getWindowKey($key));
    }

    private function getWindowKey(string $key): string
    {
        $windowStart = floor(now()->timestamp / ($this->windowsMinutes * 60));

        return "{$key}:fw:{$windowStart}";
    }

    private function getNextWindowStart(): \DateTime
    {
        $windowSeconds   = $this->windowsMinutes * 60;
        $currentWindow   = floor(now()->timestamp / $windowSeconds);
        $nextWindowStart = ($currentWindow + 1) * $windowSeconds;

        return new \DateTime("@{$nextWindowStart}");
    }
}
