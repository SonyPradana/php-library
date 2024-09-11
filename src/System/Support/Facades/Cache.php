<?php

declare(strict_types=1);

namespace System\Support\Facades;

/**
 * @method static self                         setDefaultDriver(\System\Cache\CacheInterface $driver)
 * @method static self                         setDriver(string $driver_name, \System\Cache\CacheInterface $driver)
 * @method static \System\Cache\CacheInterface driver(?string $driver = null)
 * @method static mixed                        get(string $key, mixed $default = null)
 * @method static bool                         set(string $key, mixed $value, int|\DateInterval|null $ttl = null)
 * @method static bool                         delete(string $key)
 * @method static bool                         clear()
 * @method static iterable                     getMultiple(iterable $keys, mixed $default = null)
 * @method static bool                         setMultiple(iterable $values, int|\DateInterval|null $ttl = null)
 * @method static bool                         deleteMultiple(iterable $keys)
 * @method static bool                         has(string $key)
 * @method static int                          increment(string $key, int $value)
 * @method static int                          decrement(string $key, int $value)
 * @method static mixed                        remember(string $key, int|\DateInterval|null $ttl = null, \Closure $callback)
 */
final class Cache extends Facade
{
    protected static function getAccessor()
    {
        return 'cache';
    }
}
