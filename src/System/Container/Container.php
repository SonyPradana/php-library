<?php

declare(strict_types=1);

namespace System\Container;

use DI\Container as DIContainer;

/**
 * @implements \ArrayAccess<string|class-string<mixed>, mixed>
 */
class Container extends DIContainer implements \ArrayAccess
{
    /**
     * Offest exist check.
     *
     * @param string $offset
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Get the value.
     *
     * @param string|class-string<mixed> $offset entry name or a class name
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->make($offset);
    }

    /**
     * Set the value.
     *
     * @param string $offset
     * @param mixed  $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * Unset the value.
     *
     * @param string $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->resolvedEntries[$offset]);
    }
}
