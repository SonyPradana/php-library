<?php

declare(strict_types=1);

namespace System\Cache\Storage;

use System\Cache\CacheInterface;
use System\Cache\Exceptions\CacheException;

class MemcachedStorage implements CacheInterface
{
    public function __construct(
        private \Memcached $memcached,
        private int $defaultTTL = 3_600,
    ) {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        try {
            $value = $this->memcached->get($this->normalizeKey($key));

            if ($this->memcached->getResultCode() === \Memcached::RES_NOTFOUND) {
                return $default;
            }

            return $value;
        } catch (\MemcachedException $e) {
            throw new CacheException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    public function set(string $key, mixed $value, int|\DateInterval|null $ttl = null): bool
    {
        try {
            return $this->memcached->set($this->normalizeKey($key), $value, $this->calculateExpiration($ttl));
        } catch (\MemcachedException $e) {
            throw new CacheException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    public function delete(string $key): bool
    {
        try {
            return $this->memcached->delete($this->normalizeKey($key));
        } catch (\MemcachedException $e) {
            throw new CacheException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    public function clear(): bool
    {
        try {
            return $this->memcached->flush();
        } catch (\MemcachedException $e) {
            throw new CacheException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $keys_array = is_array($keys) ? $keys : iterator_to_array($keys);

        if (count($keys_array) === 0) {
            return [];
        }

        // Map original keys to normalized keys
        $normalizedKeysMap = [];
        foreach ($keys_array as $key) {
            $normalizedKeysMap[$this->normalizeKey($key)] = $key;
        }

        try {
            $values = $this->memcached->getMulti(array_keys($normalizedKeysMap));

            if ($values === false) {
                $values = [];
            }

            $result = [];
            foreach ($normalizedKeysMap as $normalizedKey => $originalKey) {
                $result[$originalKey] = array_key_exists($normalizedKey, $values) ? $values[$normalizedKey] : $default;
            }

            return $result;
        } catch (\MemcachedException $e) {
            throw new CacheException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    public function setMultiple(iterable $values, int|\DateInterval|null $ttl = null): bool
    {
        $normalizedValues = [];
        foreach ($values as $key => $value) {
            $normalizedValues[$this->normalizeKey((string) $key)] = $value;
        }

        try {
            return $this->memcached->setMulti($normalizedValues, $this->calculateExpiration($ttl));
        } catch (\MemcachedException $e) {
            throw new CacheException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $keys_array = is_array($keys) ? $keys : iterator_to_array($keys);

        if (count($keys_array) === 0) {
            return true;
        }

        $normalizedKeys = array_map([$this, 'normalizeKey'], $keys_array);

        try {
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
        } catch (\MemcachedException $e) {
            throw new CacheException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    public function has(string $key): bool
    {
        try {
            $this->memcached->get($this->normalizeKey($key));

            return $this->memcached->getResultCode() !== \Memcached::RES_NOTFOUND;
        } catch (\MemcachedException $e) {
            throw new CacheException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    public function increment(string $key, int $value): int
    {
        $normalizedKey = $this->normalizeKey($key);
        try {
            $result = $this->memcached->increment($normalizedKey, $value);

            if ($result === false && $this->memcached->getResultCode() === \Memcached::RES_NOTFOUND) {
                $this->set($key, $value, 0);

                return $value;
            }

            return (int) $result;
        } catch (\MemcachedException $e) {
            throw new CacheException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    public function decrement(string $key, int $value): int
    {
        $normalizedKey = $this->normalizeKey($key);
        try {
            $result = $this->memcached->decrement($normalizedKey, $value);

            if ($result === false && $this->memcached->getResultCode() === \Memcached::RES_NOTFOUND) {
                $this->set($key, -$value, 0);

                return -$value;
            }

            return (int) $result;
        } catch (\MemcachedException $e) {
            throw new CacheException($e->getMessage(), (int) $e->getCode(), $e);
        }
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
     * Normalize the cache key to satisfy Memcached requirements.
     */
    protected function normalizeKey(string $key): string
    {
        // Memcached key limit is 250 characters.
        // Also contains no control characters or whitespace.
        if (strlen($key) > 240 || preg_match('/[\s\x00-\x1F\x7F]/', $key)) {
            return 'savanna:' . sha1($key);
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
