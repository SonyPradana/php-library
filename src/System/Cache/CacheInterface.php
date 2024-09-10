<?php

/**
 * Part of psr simple cache.
 *
 * @url https://github.com/php-fig/simple-cache/blob/master/src/CacheInterface.php
 */

declare(strict_types=1);

namespace System\Cache;

interface CacheInterface
{
    /**
     * Fetches a value from the cache.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     */
    public function set(string $key, mixed $value, int|\DateInterval|null $ttl = null): bool;

    /**
     * Delete an item from the cache by its unique key.
     */
    public function delete(string $key): bool;

    /**
     * Wipes clean the entire cache's keys.
     */
    public function clear(): bool;

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable<string> $keys a list of keys that can be obtained in a single operation
     *
     * @return iterable<string, mixed> A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable;

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable<string, mixed> $values
     */
    public function setMultiple(iterable $values, int|\DateInterval|null $ttl = null): bool;

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable<string> $keys a list of string-based keys to be deleted
     */
    public function deleteMultiple(iterable $keys): bool;

    /**
     * Determines whether an item is present in the cache.
     */
    public function has(string $key): bool;

    public function increment(string $key, int $value): int;

    public function decrement(string $key, int $value): int;

    public function remember(string $key, int|\DateInterval|null $ttl = null, \Closure $callback): mixed;
}
