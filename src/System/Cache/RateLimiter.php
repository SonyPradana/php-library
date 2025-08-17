<?php

declare(strict_types=1);

namespace System\Cache;

class RateLimiter
{
    private const LOCKOUT_SUFFIX = ':lockout';

    public function __construct(private CacheInterface $cache)
    {
    }

    public function isBlocked(string $key, int $maxAttempts, int|\DateInterval $decay): bool
    {
        $lockoutKey = $key . self::LOCKOUT_SUFFIX;
        $isLockout  = $this->cache->has($lockoutKey);

        if ($this->getCount($key) > $maxAttempts || $isLockout) {
            if ($isLockout) {
                return true;
            }

            $lockoutExpiry = now()->timestamp + $this->convertToSeconds($decay);
            $this->cache->set($lockoutKey, $lockoutExpiry, $decay);
        }

        return false;
    }

    public function consume(string $key, int $decayMinutes = 1): int
    {
        $this->cache->remember($key, $decayMinutes, fn () => 0);

        return $this->cache->increment($key, 1);
    }

    public function getCount(string $key): int
    {
        return (int) $this->cache->get($key, 0);
    }

    public function getRetryAfter(string $key): int
    {
        $lockoutExpiry =$this->cache->get($key . self::LOCKOUT_SUFFIX);
        if (null === $lockoutExpiry) {
            return 0;
        }

        return max(0, (int) $lockoutExpiry - now()->timestamp);
    }

    public function remaining(string $key, int $maxAttempts): int
    {
        $attempts = $this->consume($key);

        return 0 === $attempts ? $maxAttempts : $maxAttempts - $attempts + 1;
    }

    private function convertToSeconds(int|\DateInterval $decay): int
    {
        if ($decay instanceof \DateInterval) {
            return (new \DateTime())->add($decay)->getTimestamp() - now()->timestamp;
        }

        return $decay * 60;
    }
}
