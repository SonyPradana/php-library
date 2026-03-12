<?php

declare(strict_types=1);

namespace System\Cache\Storage;

use System\Cache\CacheInterface;
use System\Cache\Exceptions\InvalidCacheArgumentException;

class StackStorage implements CacheInterface
{
    /**
     * @var CacheInterface[]
     */
    private array $drivers;

    /**
     * @var bool[]
     */
    private array $healthy;

    /**
     * @var array<int, \Throwable>
     */
    private array $lastExceptions = [];

    /**
     * @param CacheInterface[] $drivers
     */
    public function __construct(array $drivers)
    {
        if ($drivers === []) {
            throw new InvalidCacheArgumentException('StackStorage requires at least one cache driver.');
        }

        $this->drivers = array_values($drivers);
        $this->healthy = array_fill(0, count($this->drivers), true);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        foreach ($this->healthyDrivers() as $index => $driver) {
            $value = $this->tryCall($index, static function () use ($driver, $key): mixed {
                return $driver->get($key, null);
            });

            if (null !== $value) {
                return $value;
            }
        }

        return $default;
    }

    public function set(string $key, mixed $value, \DateInterval|int|null $ttl = null): bool
    {
        return $this->broadcast(static function (CacheInterface $d) use ($key, $value, $ttl): bool {
            return $d->set($key, $value, $ttl);
        });
    }

    public function delete(string $key): bool
    {
        return $this->broadcast(static function (CacheInterface $d) use ($key): bool {
            return $d->delete($key);
        });
    }

    public function clear(): bool
    {
        return $this->broadcast(static function (CacheInterface $d): bool {
            return $d->clear();
        });
    }

    /**
     * @param iterable<string> $keys
     *
     * @return iterable<string, mixed>
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        /** @var string[] $keys */
        $keys    = is_array($keys) ? $keys : iterator_to_array($keys);
        $result  = array_fill_keys($keys, $default);
        $missing = $keys;

        foreach ($this->healthyDrivers() as $index => $driver) {
            if ($missing === []) {
                break;
            }

            /** @var array<string, mixed>|null $fetched */
            $fetched = $this->tryCall($index, static function () use ($driver, $missing): mixed {
                return $driver->getMultiple($missing, null);
            });

            if (null === $fetched) {
                continue;
            }

            $stillMissing = [];

            foreach ($missing as $key) {
                $value = $fetched[$key] ?? null;

                if (null !== $value) {
                    $result[$key] = $value;
                } else {
                    $stillMissing[] = $key;
                }
            }

            $missing = $stillMissing;
        }

        return $result;
    }

    /**
     * @param iterable<string, mixed> $values
     */
    public function setMultiple(iterable $values, \DateInterval|int|null $ttl = null): bool
    {
        /** @var array<string, mixed> $values */
        $values = is_array($values) ? $values : iterator_to_array($values);

        return $this->broadcast(static function (CacheInterface $d) use ($values, $ttl): bool {
            return $d->setMultiple($values, $ttl);
        });
    }

    /**
     * @param iterable<string> $keys
     */
    public function deleteMultiple(iterable $keys): bool
    {
        /** @var string[] $keys */
        $keys = is_array($keys) ? $keys : iterator_to_array($keys);

        return $this->broadcast(static function (CacheInterface $d) use ($keys): bool {
            return $d->deleteMultiple($keys);
        });
    }

    public function has(string $key): bool
    {
        foreach ($this->healthyDrivers() as $index => $driver) {
            $result = $this->tryCall($index, static function () use ($driver, $key): bool {
                return $driver->has($key);
            });

            if (true === $result) {
                return true;
            }
        }

        return false;
    }

    public function increment(string $key, int $value): int
    {
        return $this->firstHealthy(
            static function (CacheInterface $d) use ($key, $value): int {
                return $d->increment($key, $value);
            },
            'increment'
        );
    }

    public function decrement(string $key, int $value): int
    {
        return $this->firstHealthy(
            static function (CacheInterface $d) use ($key, $value): int {
                return $d->decrement($key, $value);
            },
            'decrement'
        );
    }

    public function remember(string $key, \DateInterval|int|null $ttl, \Closure $callback): mixed
    {
        $value = $this->get($key);

        if (null !== $value) {
            return $value;
        }

        $this->set($key, $value = $callback(), $ttl);

        return $value;
    }

    public function recover(int $index): void
    {
        if (array_key_exists($index, $this->drivers)) {
            $this->healthy[$index] = true;
        }
    }

    /**
     * @return bool[]
     */
    public function getHealthMap(): array
    {
        return $this->healthy;
    }

    /**
     * @return array<int, \Throwable>
     */
    public function getLastExceptions(): array
    {
        return $this->lastExceptions;
    }

    // Internals --------------------------------------

    /**
     * @internal
     *
     * @return \Generator<int, CacheInterface>
     */
    private function healthyDrivers(): \Generator
    {
        foreach ($this->drivers as $index => $driver) {
            if ($this->healthy[$index]) {
                yield $index => $driver;
            }
        }
    }

    /**
     * @internal
     *
     * @param \Closure(): mixed $callback
     */
    private function tryCall(int $index, \Closure $callback): mixed
    {
        try {
            return $callback();
        } catch (\Throwable $e) {
            $this->healthy[$index]        = false;
            $this->lastExceptions[$index] = $e;

            return null;
        }
    }

    /**
     * @internal
     *
     * @param \Closure(CacheInterface): bool $callback
     */
    private function broadcast(\Closure $callback): bool
    {
        $result = false;

        foreach ($this->healthyDrivers() as $index => $driver) {
            $succeeded = $this->tryCall($index, static function () use ($callback, $driver): bool {
                return $callback($driver);
            });

            $result = (true === $succeeded) || $result;
        }

        return $result;
    }

    /**
     * @internal
     *
     * @param \Closure(CacheInterface): int $callback
     */
    private function firstHealthy(\Closure $callback, string $operation): int
    {
        foreach ($this->healthyDrivers() as $index => $driver) {
            /** @var int|null $result */
            $result = $this->tryCall($index, static function () use ($callback, $driver): int {
                return $callback($driver);
            });

            if (null !== $result) {
                return $result;
            }
        }

        $previous = count($this->lastExceptions) > 0
            ? $this->lastExceptions[array_key_last($this->lastExceptions)]
            : null;

        throw new \RuntimeException(sprintf('All cache drivers are unhealthy. Cannot perform "%s".', $operation), 0, $previous);
    }
}
