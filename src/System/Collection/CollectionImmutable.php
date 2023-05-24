<?php

namespace System\Collection;

use System\Collection\Exceptions\NoModify;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @extends AbstractCollectionImmutable<TKey, TValue>
 */
class CollectionImmutable extends AbstractCollectionImmutable
{
    /**
     * @throws NoModify
     */
    public function offsetSet($offset, $value): void
    {
        throw new NoModify();
    }

    /**
     * @throws NoModify
     */
    public function offsetUnset($offset): void
    {
        throw new NoModify();
    }
}
