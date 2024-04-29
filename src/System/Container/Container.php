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
     * @param string $offset
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * @param string|class-string<mixed> $offset entry name or a class name
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @param string $offset
     * @param mixed  $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset): void
    {
    }
}
