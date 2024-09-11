<?php

declare(strict_types=1);

namespace System\Cache\Storage;

use System\Cache\CacheInterface;

class ArrayStorage implements CacheInterface
{
    /**
     * @var array<string, array{value: mixed, timestamp?: int, mtime?: float}>
     */
    protected array $storage = [];

    public function __construct(private int $defaultTTL = 3_600)
    {
    }

    /**
     * Get info of storage.
     *
     * @return array<string, array{value: mixed, timestamp?: int, mtime?: float}>
     */
    public function getInfo(string $key): array
    {
        return $this->storage[$key] ?? [];
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (false === array_key_exists($key, $this->storage)) {
            return $default;
        }

        $item = $this->storage[$key];

        $expiresAt = $item['timestamp'] ?? 0;

        if ($this->isExpired($expiresAt)) {
            $this->delete($key);

            return $default;
        }

        return $item['value'];
    }

    public function set(string $key, mixed $value, int|\DateInterval|null $ttl = null): bool
    {
        $this->storage[$key] = [
            'value'     => $value,
            'timestamp' => $this->calculateExpirationTimestamp($ttl),
            'mtime'     => $this->createMtime(),
        ];

        return true;
    }

    public function delete(string $key): bool
    {
        if ($this->has($key)) {
            unset($this->storage[$key]);

            return true;
        }

        return false;
    }

    public function clear(): bool
    {
        $this->storage = [];

        return true;
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
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }

        return false;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $state = null;

        foreach ($keys as $key) {
            $result = $this->delete($key);

            $state = null === $state ? $result : $result && $state;
        }

        return $state ?: false;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->storage);
    }

    public function increment(string $key, int $value): int
    {
        if (false === $this->has($key)) {
            $this->set($key, $value, 0);

            return $this->storage[$key]['value'];
        }

        $this->storage[$key]['value'] = ((int) $this->storage[$key]['value']) + $value;

        return $this->storage[$key]['value'];
    }

    public function decrement(string $key, int $value): int
    {
        return $this->increment($key, $value * -1);
    }

    public function remember(string $key, int|\DateInterval|null $ttl = null, \Closure $callback): mixed
    {
        $value = $this->get($key);

        if (null !== $value) {
            return $value;
        }

        $this->set($key, $value = $callback(), $ttl);

        return $value;
    }

    private function calculateExpirationTimestamp(int|\DateInterval|\DateTimeInterface|null $ttl): int
    {
        if ($ttl instanceof \DateInterval) {
            return (new \DateTimeImmutable())->add($ttl)->getTimestamp();
        }

        if ($ttl instanceof \DateTimeInterface) {
            return $ttl->getTimestamp();
        }

        $ttl ??= $this->defaultTTL;

        return (new \DateTimeImmutable())->add(new \DateInterval("PT{$ttl}S"))->getTimestamp();
    }

    private function isExpired(int $timestamp): bool
    {
        return $timestamp !== 0 && time() >= $timestamp;
    }

    /**
     * Calculate the microtime based on the current time and microtime.
     */
    private function createMtime(): float
    {
        $currentTime = time();
        $microtime   = microtime(true);

        $fractionalPart = $microtime - $currentTime;

        if ($fractionalPart >= 1) {
            $currentTime += (int) $fractionalPart;
            $fractionalPart -= (int) $fractionalPart;
        }

        $mtime = $currentTime + $fractionalPart;

        return round($mtime, 3);
    }
}
