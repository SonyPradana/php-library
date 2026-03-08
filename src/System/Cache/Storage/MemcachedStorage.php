<?php

declare(strict_types=1);

namespace System\Cache\Storage;

use System\Cache\CacheInterface;

class MemcachedStorage implements CacheInterface
{
    public function __construct(
        private \Memcached $memcached,
        private int $defaultTTL = 3_600,
    ) {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->memcached->get($key);

        if ($this->memcached->getResultCode() === \Memcached::RES_NOTFOUND) {
            return $default;
        }

        return $value;
    }

    public function set(string $key, mixed $value, int|\DateInterval|null $ttl = null): bool
    {
        return $this->memcached->set($key, $value, $this->calculateExpiration($ttl));
    }

    public function delete(string $key): bool
    {
        return $this->memcached->delete($key);
    }

    public function clear(): bool
    {
        return $this->memcached->flush();
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $keys_array = is_array($keys) ? $keys : iterator_to_array($keys);

        if (count($keys_array) === 0) {
            return [];
        }

        $values = $this->memcached->getMulti($keys_array);

        if ($values === false) {
            $values = [];
        }

        $result = [];
        foreach ($keys_array as $key) {
            $result[$key] = array_key_exists($key, $values) ? $values[$key] : $default;
        }

        return $result;
    }

    public function setMultiple(iterable $values, int|\DateInterval|null $ttl = null): bool
    {
        $values_array = is_array($values) ? $values : iterator_to_array($values);

        return $this->memcached->setMulti($values_array, $this->calculateExpiration($ttl));
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $keys_array = is_array($keys) ? $keys : iterator_to_array($keys);

        if (count($keys_array) === 0) {
            return true;
        }

        /** @var array<string, bool|int>|bool $results */
        $results = $this->memcached->deleteMulti($keys_array);

        if (is_array($results)) {
            foreach ($results as $result) {
                if ($result !== true && $result !== \Memcached::RES_SUCCESS) {
                    return false;
                }
            }

            return true;
        }

        return (bool) $results;
    }

    public function has(string $key): bool
    {
        $this->memcached->get($key);

        return $this->memcached->getResultCode() !== \Memcached::RES_NOTFOUND;
    }

    public function increment(string $key, int $value): int
    {
        // Memcached::increment fails if key doesn't exist.
        // We'll try to increment, and if it fails because it doesn't exist, we'll set it.
        $result = $this->memcached->increment($key, $value);

        if ($result === false && $this->memcached->getResultCode() === \Memcached::RES_NOTFOUND) {
            $this->set($key, $value, 0);

            return $value;
        }

        return (int) $result;
    }

    public function decrement(string $key, int $value): int
    {
        // Memcached::decrement also fails if key doesn't exist.
        $result = $this->memcached->decrement($key, $value);

        if ($result === false && $this->memcached->getResultCode() === \Memcached::RES_NOTFOUND) {
            $this->set($key, -$value, 0);

            return -$value;
        }

        return (int) $result;
    }

    public function remember(string $key, int|\DateInterval|null $ttl, \Closure $callback): mixed
    {
        $value = $this->get($key);

        if (null !== $value) {
            return $value;
        }

        $this->set($key, $value = $callback(), $ttl);

        return $value;
    }

    /**
     * Calculate the expiration time for Memcached.
     */
    protected function calculateExpiration(int|\DateInterval|null $ttl): int
    {
        if ($ttl instanceof \DateInterval) {
            $ttl = (int) (new \DateTimeImmutable())->add($ttl)->getTimestamp() - time();
        }

        $ttl ??= $this->defaultTTL;

        // Memcached treats TTL > 30 days (2592000 seconds) as unix timestamp.
        if ($ttl > 2_592_000) {
            return time() + $ttl;
        }

        return $ttl;
    }
}
