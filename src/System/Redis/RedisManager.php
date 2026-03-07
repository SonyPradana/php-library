<?php

declare(strict_types=1);

namespace System\Redis;

class RedisManager implements RedisInterface
{
    /** @var array<string, RedisInterface|\Closure(): RedisInterface> */
    private $driver = [];

    private RedisInterface $default_driver;

    public function __construct()
    {
    }

    public function setDefaultDriver(RedisInterface $driver): self
    {
        $this->default_driver = $driver;

        return $this;
    }

    /**
     * @param RedisInterface|\Closure(): RedisInterface $driver
     */
    public function setDriver(string $driver_name, $driver): self
    {
        $this->driver[$driver_name] = $driver;

        return $this;
    }

    private function resolve(string $driver_name): RedisInterface
    {
        $driver = $this->driver[$driver_name];

        if (\is_callable($driver)) {
            $driver = $driver();
        }

        if (null === $driver) {
            throw new \Exception("Can not use driver {$driver_name}.");
        }

        return $this->driver[$driver_name] = $driver;
    }

    public function driver(?string $driver_name = null): RedisInterface
    {
        if ($driver_name === null) {
            return $this->default_driver;
        }

        if (isset($this->driver[$driver_name])) {
            return $this->resolve($driver_name);
        }

        return $this->default_driver;
    }

    // --- interfaces

    /**
     * {@inheritdoc}
     */
    public function get(string $key): mixed
    {
        return $this->driver()->get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, mixed $value, ?int $timeout = null): bool
    {
        return $this->driver()->set($key, $value, $timeout);
    }

    /**
     * {@inheritdoc}
     */
    public function del(string|array $keys): int
    {
        return $this->driver()->del($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function exists(string $key): bool
    {
        return $this->driver()->exists($key);
    }

    /**
     * {@inheritdoc}
     */
    public function incr(string $key): int|false
    {
        return $this->driver()->incr($key);
    }

    /**
     * {@inheritdoc}
     */
    public function decr(string $key): int|false
    {
        return $this->driver()->decr($key);
    }

    /**
     * {@inheritdoc}
     */
    public function keys(string $pattern): array
    {
        return $this->driver()->keys($pattern);
    }

    /**
     * {@inheritdoc}
     */
    public function client(): object
    {
        return $this->driver()->client();
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): ?string
    {
        return $this->driver()->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function disconnect(): void
    {
        $this->driver()->disconnect();
    }

    /**
     * {@inheritdoc}
     */
    public function command(string $command, array $arguments = []): mixed
    {
        return $this->driver()->command($command, (array) $arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function __call(string $method, array $arguments): mixed
    {
        return $this->driver()->{$method}(...((array) $arguments));
    }
}
