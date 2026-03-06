<?php

declare(strict_types=1);

namespace System\Redis;

interface RedisInterface
{
    /**
     * Get value from redis.
     */
    public function get(string $key): mixed;

    /**
     * Set value to redis.
     */
    public function set(string $key, mixed $value, ?int $timeout = null): bool;

    /**
     * Delete from redis.
     *
     * @param list<string>|string $keys
     */
    public function del(string|array $keys): int;

    /**
     * Check key exists.
     */
    public function exists(string $key): bool;

    /**
     * Increment value.
     */
    public function incr(string $key): int|false;

    /**
     * Decrement value.
     */
    public function decr(string $key): int|false;

    /**
     * Get redis keys.
     *
     * @return list<string>
     */
    public function keys(string $pattern): array;

    /**
     * Get redis client connection.
     */
    public function client(): object;

    /**
     * Get connection name.
     */
    public function getName(): ?string;

    /**
     * Disconnect from redis.
     */
    public function disconnect(): void;

    /**
     * Call redis raw command.
     *
     * @param list<mixed> $arguments
     */
    public function command(string $command, array $arguments = []): mixed;

    /**
     * Call redis raw command from magic method.
     *
     * @param list<mixed> $arguments
     */
    public function __call(string $method, array $arguments): mixed;
}
