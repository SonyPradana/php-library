<?php

declare(strict_types=1);

namespace System\Cache;

use System\Cache\Storage\ArrayStorage;

class CacheManager implements CacheInterface
{
    /** @var array<string, CacheInterface> */
    private $driver = [];

    private CacheInterface $default_driver;

    public function __construct()
    {
        $this->setDefaultDriver(new ArrayStorage());
    }

    public function setDefaultDriver(CacheInterface $driver): self
    {
        $this->default_driver = $driver;

        return $this;
    }

    public function setDriver(string $driver_name, CacheInterface $driver): self
    {
        $this->driver[$driver_name] = $driver;

        return $this;
    }

    public function driver(?string $driver = null): CacheInterface
    {
        if (array_key_exists($driver, $this->driver)) {
            return $this->driver[$driver];
        }

        return $this->default_driver;
    }

    /**
     * @param mixed[] $parameters
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->driver()->{$method}(...$parameters);
    }

    // ---

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->driver()->get($key, $default);
    }

    public function set(string $key, mixed $value, int|\DateInterval|null $ttl = null): bool
    {
        return $this->driver()->set($key, $value, $ttl);
    }

    public function delete(string $key): bool
    {
        return $this->driver()->delete($key);
    }

    public function clear(): bool
    {
        return $this->driver()->clear();
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        return $this->driver()->getMultiple($keys, $default);
    }

    public function setMultiple(iterable $values, int|\DateInterval|null $ttl = null): bool
    {
        return $this->driver()->setMultiple($values, $ttl);
    }

    public function deleteMultiple(iterable $keys): bool
    {
        return $this->driver()->deleteMultiple($keys);
    }

    public function has(string $key): bool
    {
        return $this->driver()->has($key);
    }
}
