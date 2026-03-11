<?php

declare(strict_types=1);

namespace System\Cache\Storage;

use System\Cache\CacheInterface;
use System\Cache\Exceptions\InvalidCacheArgumentException;
use System\Cache\Exceptions\UnsupportedCacheDriverException;

class ApcuStorage implements CacheInterface
{
    public function __construct(
        private string $prefix = '',
        private int $defaultTTL = 3_600,
    ) {
        if (false === static::isSupported()) {
            throw new UnsupportedCacheDriverException('APCu extension is not loaded or enabled.');
        }
    }

    public static function isSupported(): bool
    {
        return \extension_loaded('apcu') && \apcu_enabled();
    }

    /**
     * Get info of storage.
     *
     * @return array<string, array{value: mixed, timestamp?: int, mtime?: float}>
     */
    public function getInfo(string $key): array
    {
        /** @var array<string, mixed>|false $info */
        $info = \apcu_key_info($this->prefix . $key);

        if (false === $info) {
            return [];
        }

        return [
            'value'     => $this->get($key),
            'timestamp' => $info['ttl'] > 0 ? $info['creation_time'] + $info['ttl'] : 0,
            'mtime'     => (float) $info['mtime'],
        ];
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $success = false;
        $value   = \apcu_fetch($this->prefix . $key, $success);

        return $success ? $value : $default;
    }

    public function set(string $key, mixed $value, int|\DateInterval|null $ttl = null): bool
    {
        return \apcu_store($this->prefix . $key, $value, $this->calculateTTL($ttl));
    }

    public function delete(string $key): bool
    {
        return \apcu_delete($this->prefix . $key);
    }

    public function clear(): bool
    {
        return \apcu_clear_cache();
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $prefixedKeys = [];
        foreach ($keys as $key) {
            $prefixedKeys[] = $this->prefix . $key;
        }

        $values = \apcu_fetch($prefixedKeys);
        $result = [];

        foreach ($keys as $key) {
            $prefixedKey  = $this->prefix . $key;
            $result[$key] = array_key_exists($prefixedKey, $values) ? $values[$prefixedKey] : $default;
        }

        return $result;
    }

    public function setMultiple(iterable $values, int|\DateInterval|null $ttl = null): bool
    {
        $prefixedValues = [];
        foreach ($values as $key => $value) {
            $prefixedValues[$this->prefix . $key] = $value;
        }

        $result = \apcu_store($prefixedValues, null, $this->calculateTTL($ttl));

        return empty($result);
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $prefixedKeys = [];
        foreach ($keys as $key) {
            $prefixedKeys[] = $this->prefix . $key;
        }

        $result = \apcu_delete($prefixedKeys);

        return empty($result);
    }

    public function has(string $key): bool
    {
        return \apcu_exists($this->prefix . $key);
    }

    public function increment(string $key, int $value): int
    {
        if ($this->has($key) && false === is_int($this->get($key))) {
            throw new InvalidCacheArgumentException('Value increment must be integer.');
        }

        $result = \apcu_inc($this->prefix . $key, $value, $success);

        if (false === $success) {
            $this->set($key, $value, 0);

            return $value;
        }

        return $result;
    }

    public function decrement(string $key, int $value): int
    {
        return $this->increment($key, $value * -1);
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

    private function calculateTTL(int|\DateInterval|null $ttl): int
    {
        if ($ttl instanceof \DateInterval) {
            $now = new \DateTimeImmutable();

            return $now->add($ttl)->getTimestamp() - $now->getTimestamp();
        }

        return $ttl ?? $this->defaultTTL;
    }
}
