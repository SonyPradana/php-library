<?php

declare(strict_types=1);

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
     * {@inheritdoc}
     *
     * @throws NoModify
     */
    public function offsetSet($offset, $value): void
    {
        throw new NoModify();
    }

    /**
     * {@inheritdoc}
     *
     * @throws NoModify
     */
    public function offsetUnset($offset): void
    {
        throw new NoModify();
    }
}
