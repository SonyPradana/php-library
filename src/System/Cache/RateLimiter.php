<?php

declare(strict_types=1);

namespace System\Cache;

class RateLimiter
{
    private const LOCKOUT_SUFFIX = ':lockout';

    public function __construct(private CacheInterface $cache)
    {
    }

    public function isThrottled(string $key, int $maxAttempts, int|\DateTime $decay): bool
    {
        $lockoutKey = $key . self::LOCKOUT_SUFFIX;
        $isLockout  = $this->cache->has($lockoutKey);

        if ($this->getAttempts($key) > $maxAttempts || $isLockout) {
            if ($isLockout) {
                return true;
            }

            $lockoutExpiry = now()->timestamp + $this->convertToSeconds($decay);
            $this->cache->set($lockoutKey, $lockoutExpiry, $decay);
        }

        return false;
    }

    public function recordAttempt(string $key, int $decayMinutes = 1): int
    {
        $this->cache->remember($key, $decayMinutes, fn () => 0);

        return $this->cache->increment($key, 1);
    }

    public function getAttempts(string $key): int
    {
        return (int) $this->cache->get($key, 0);
    }

    public function getRemainingTime(string $key): int
    {
        $lockoutExpiry =$this->cache->get($key . self::LOCKOUT_SUFFIX);
        if (null === $lockoutExpiry) {
            return 0;
        }

        return max(0, (int) $lockoutExpiry - now()->timestamp);
    }

    public function recordAttemptLeft(string $key, int $maxAttempts): int
    {
        $attempts = $this->recordAttempt($key);

        return 0 === $attempts ? $maxAttempts : $maxAttempts - $attempts + 1
    }

    private function convertToSeconds(int|\DateTime $decay): int
    {
        if ($decay instanceof \DateInterval) {
            return (new \DateTime())->add($decay)->getTimestamp() - now()->timestamp;
        }

        return $decay * 60;
    }
}
