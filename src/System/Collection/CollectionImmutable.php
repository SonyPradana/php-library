<?php

namespace System\Collection;

use System\Collection\Exceptions\NoModify;

/**
 * @template T
 *
 * @extends AbstractCollectionImmutable<T>
 */
class CollectionImmutable extends AbstractCollectionImmutable
{
    // same as perent

    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    public function offsetSet($offset, $value): void
    {
        throw new NoModify();
    }

    public function offsetUnset($offset): void
    {
        throw new NoModify();
    }

    /**
     * @return \ArrayIterator<array-key, T>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->all());
    }
}
