<?php

declare(strict_types=1);

namespace System\Container;

use DI\Container as DIContainer;

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
     * @template T
     *
     * @param string|class-string<T> $offset entry name or a class name
     *
     * @return mixed|T
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @param string                 $offset
     * @param mixed|DefinitionHelper $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset): void
    {
    }
}
