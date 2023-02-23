<?php

declare(strict_types=1);

namespace System\Collection\Interfaces;

/**
 * @template T
 *
 * @extends \ArrayAccess<array-key, T>
 * @extends \IteratorAggregate<T>
 */
interface CollectionInterface extends \ArrayAccess, \IteratorAggregate, \Countable
{
}
