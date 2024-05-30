<?php

declare(strict_types=1);

namespace System\Integrate;

/**
 * @implements \ArrayAccess<string, mixed>
 */
class ConfigRepository implements \ArrayAccess
{
    /**
     * Create new config using array.
     *
     * @param array<string, mixed> $config
     */
    public function __construct(protected $config = [])
    {
    }

    /**
     * Checks if the given key or index exists in the config.
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->config);
    }

    /**
     * Get config.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Set new or create config.
     */
    public function set(string $key, mixed $value): void
    {
        $this->config[$key] = $value;
    }

    /**
     * Push value in an array items.
     */
    public function push(string $key, mixed $value): void
    {
        $array   = $this->get($key, []);
        $array[] = $value;
        $this->set($key, $array);
    }

    /**
     * Convert back to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->config;
    }

    // array access

    /**
     * Checks if the given key or index exists in the config.
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Get config.
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * Set new or create config.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * Unset or set to null.
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->set($offset, null);
    }
}
