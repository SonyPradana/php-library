<?php

declare(strict_types=1);

namespace System\Cache\Storage;

use System\Cache\CacheInterface;
use System\Cache\Exceptions\CacheException;
use System\Cache\Exceptions\InvalidCacheArgumentException;
use System\Cache\Exceptions\UnsupportedCacheDriverException;

class MemcachedStorage implements CacheInterface
{
    private string $prefix_key = 'memcached-storage:';

    /**
     * @param \Memcached $memcached
     */
    public function __construct(
        private object $memcached,
        private int $defaultTTL = 3_600,
    ) {
        /** @var class-string $class */
        $class = '\Memcached';

        if (false === class_exists($class) || false === ($memcached instanceof $class)) {
            throw new InvalidCacheArgumentException('The memcached must be an instance of \Memcached.');
        }

        $this->memcached = $memcached;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->call(function () use ($key, $default) {
            $value = $this->memcached->get($this->normalizeKey($key));

            return $this->memcached->getResultCode() === \Memcached::RES_NOTFOUND
                ? $default
                : $value;
        });
    }

    public function set(string $key, mixed $value, int|\DateInterval|null $ttl = null): bool
    {
        return $this->call(fn () => $this->memcached->set($this->normalizeKey($key), $value, $this->calculateExpiration($ttl)));
    }

    public function delete(string $key): bool
    {
        return $this->call(fn () => $this->memcached->delete($this->normalizeKey($key)));
    }

    public function clear(): bool
    {
        return $this->call(fn () => $this->memcached->flush());
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $keys_array = is_array($keys) ? $keys : iterator_to_array($keys);

        if (0 === count($keys_array)) {
            return [];
        }

        $normalizedKeysMap = [];
        foreach ($keys_array as $key) {
            $normalizedKeysMap[$this->normalizeKey($key)] = $key;
        }

        return $this->call(function () use ($normalizedKeysMap, $default) {
            $values = $this->memcached->getMulti(array_keys($normalizedKeysMap)) ?: [];

            $result = [];
            foreach ($normalizedKeysMap as $normalizedKey => $originalKey) {
                $result[$originalKey] = array_key_exists($normalizedKey, $values)
                    ? $values[$normalizedKey]
                    : $default;
            }

            return $result;
        });
    }

    public function setMultiple(iterable $values, int|\DateInterval|null $ttl = null): bool
    {
        $normalizedValues = [];
        foreach ($values as $key => $value) {
            $normalizedValues[$this->normalizeKey((string) $key)] = $value;
        }

        return $this->call(fn () => $this->memcached->setMulti($normalizedValues, $this->calculateExpiration($ttl)));
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $keys_array = is_array($keys) ? $keys : iterator_to_array($keys);

        if (0 === count($keys_array)) {
            return true;
        }

        $normalizedKeys = array_map([$this, 'normalizeKey'], $keys_array);

        return $this->call(function () use ($normalizedKeys) {
            /** @var array<string, bool|int>|bool $results */
            $results = $this->memcached->deleteMulti($normalizedKeys);

            if (is_array($results)) {
                foreach ($results as $result) {
                    if ($result !== true && $result !== \Memcached::RES_SUCCESS) {
                        return false;
                    }
                }

                return true;
            }

            return (bool) $results;
        });
    }

    public function has(string $key): bool
    {
        return $this->call(function () use ($key) {
            $this->memcached->get($this->normalizeKey($key));

            return $this->memcached->getResultCode() !== \Memcached::RES_NOTFOUND;
        });
    }

    public function increment(string $key, int $value): int
    {
        return $this->call(function () use ($key, $value) {
            $result = $this->memcached->increment($this->normalizeKey($key), $value);

            if ($result === false && $this->memcached->getResultCode() === \Memcached::RES_NOTFOUND) {
                $this->set($key, $value, 0);

                return $value;
            }

            return (int) $result;
        });
    }

    public function decrement(string $key, int $value): int
    {
        return $this->call(function () use ($key, $value) {
            $result = $this->memcached->decrement($this->normalizeKey($key), $value);

            if ($result === false && $this->memcached->getResultCode() === \Memcached::RES_NOTFOUND) {
                $this->set($key, -$value, 0);

                return -$value;
            }

            return (int) $result;
        });
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
     * @template T
     *
     * @param callable(): T $operation
     *
     * @return T
     *
     * @throws CacheException
     */
    private function call(callable $operation): mixed
    {
        try {
            return $operation();
        } catch (\MemcachedException $e) {
            throw new UnsupportedCacheDriverException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * Normalize the cache key to satisfy Memcached requirements.
     */
    protected function normalizeKey(string $key): string
    {
        // Memcached key limit is 250 characters.
        // Also contains no control characters or whitespace.
        $keyLenghtLimit = 250 - strlen($this->prefix_key);
        if (strlen($key) > $keyLenghtLimit || preg_match('/[\s\x00-\x1F\x7F]/', $key)) {
            return $this->prefix_key . sha1($key);
        }

        return $key;
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
