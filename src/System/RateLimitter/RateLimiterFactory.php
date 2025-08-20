<?php

declare(strict_types=1);

namespace System\RateLimitter;

use System\Cache\CacheInterface;
use System\RateLimitter\Interfaces\RateLimiterInterface;
use System\RateLimitter\NoLimiter\FixedWindow;
use System\RateLimitter\NoLimiter\NoLimiter;

class RateLimiterFactory
{
    public function __construct(
        private CacheInterface $cache,
    ) {
    }

    public function createFixedWindow(int $limit, int $windowsMinutes): RateLimiterInterface
    {
        return new RateLimiter(
            new FixedWindow(
                cache: $this->cache,
                limit: $limit,
                windowsMinutes: $windowsMinutes,
            )
        );
    }

    public function createNoLimitter(): RateLimiterInterface
    {
        return new RateLimiter(
            new NoLimiter()
        );
    }
}
