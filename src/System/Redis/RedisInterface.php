<?php

declare(strict_types=1);

namespace System\Redis;

interface RedisInterface
{
    /**
     * Get the value of a key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key);

    /**
     * Set the string value of a key.
     *
     * @param string $key
     * @param mixed  $value
     * @param mixed  $timeout
     *
     * @return bool
     */
    public function set(string $key, $value, $timeout = null);

    /**
     * Delete a key.
     *
     * @param string|string[] $keys
     *
     * @return int
     */
    public function del($keys);

    /**
     * Determine if a key exists.
     *
     * @param string $key
     *
     * @return int
     */
    public function exists(string $key);

    /**
     * Increment the integer value of a key by one.
     *
     * @param string $key
     *
     * @return int
     */
    public function incr(string $key);

    /**
     * Decrement the integer value of a key by one.
     *
     * @param string $key
     *
     * @return int
     */
    public function decr(string $key);

    /**
     * Find all keys matching the given pattern.
     *
     * @param string $pattern
     *
     * @return array<int, string>
     */
    public function keys(string $pattern);

    /**
     * Runs a raw Redis command.
     *
     * @param string $command
     * @param array<int, mixed>  $arguments
     *
     * @return mixed
     */
    public function command(string $command, array $arguments = []);

    /**
     * Dynamically handle calls to the class.
     *
     * @param string $method
     * @param array<int, mixed>  $arguments
     *
     * @return mixed
     */
    public function __call(string $method, array $arguments);
}
