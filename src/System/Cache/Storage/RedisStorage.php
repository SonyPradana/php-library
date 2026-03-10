<?php

declare(strict_types=1);

namespace System\Cache\Storage;

use System\Cache\CacheInterface;
use System\Redis\RedisInterface;

class RedisStorage implements CacheInterface
{
    public function __construct(
        private RedisInterface $redis,
        private int $defaultTTL = 3600,
    ) {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->redis->get($key);

        if (false === $value) {
            return $default;
        }

        return unserialize((string) $value, ['allowed_classes' => false]);
    }

    public function set(string $key, mixed $value, int|\DateInterval|null $ttl = null): bool
    {
        $serializedValue = serialize($value);
        $seconds         = $this->calculateTTLInSeconds($ttl);

        return $this->redis->set($key, $serializedValue, $seconds);
    }

    public function delete(string $key): bool
    {
        return (bool) $this->redis->del($key);
    }

    public function clear(): bool
    {
        return (bool) $this->redis->command('flushdb');
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    public function setMultiple(iterable $values, int|\DateInterval|null $ttl = null): bool
    {
        $success = true;

        foreach ($values as $key => $value) {
            $success = $this->set($key, $value, $ttl) && $success;
        }

        return $success;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $success = true;

        foreach ($keys as $key) {
            $success = $this->delete($key) && $success;
        }

        return $success;
    }

    public function has(string $key): bool
    {
        return $this->redis->exists($key);
    }

    public function increment(string $key, int $value): int
    {
        if (false === $this->has($key)) {
            $this->set($key, $value, $this->defaultTTL);

            return $value;
        }

        $currentValue = $this->get($key);

        if (false === is_int($currentValue)) {
            throw new \InvalidArgumentException('Value to increment must be an integer.');
        }

        $newValue = $currentValue + $value;

        $this->set($key, $newValue, $this->defaultTTL);

        return $newValue;
    }

    public function decrement(string $key, int $value): int
    {
        return $this->increment($key, -1 * $value);
    }

    public function remember(string $key, int|\DateInterval|null $ttl, \Closure $callback): mixed
    {
        $value = $this->get($key);

        if (null !== $value) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);

        return $value;
    }

    private function calculateTTLInSeconds(int|\DateInterval|null $ttl): int
    {
        if (null === $ttl) {
            return $this->defaultTTL;
        }

        if ($ttl instanceof \DateInterval) {
            return (new \DateTimeImmutable())->add($ttl)->getTimestamp() - time();
        }

        return $ttl;
    }
}
