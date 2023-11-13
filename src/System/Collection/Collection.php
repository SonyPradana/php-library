<?php

declare(strict_types=1);

namespace System\Collection;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @extends AbstractCollectionImmutable<TKey, TValue>
 */
class Collection extends AbstractCollectionImmutable
{
    /**
     * @param TKey   $name
     * @param TValue $value
     */
    public function __set($name, $value): void
    {
        $this->set($name, $value);
    }

    /**
     * Add reference from collection.
     *
     * @param AbstractCollectionImmutable<TKey, TValue> $collection
     *
     * @return $this
     */
    public function ref(AbstractCollectionImmutable $collection): self
    {
        $this->add($collection->collection);

        return $this;
    }

    /**
     * @return $this
     */
    public function clear(): self
    {
        $this->collection = [];

        return $this;
    }

    /**
     * @param array<TKey, TValue> $collection
     *
     * @return $this
     */
    public function add(array $collection): self
    {
        foreach ($collection as $key => $item) {
            $this->set($key, $item);
        }

        return $this;
    }

    /**
     * @param TKey $name
     *
     * @return $this
     */
    public function remove($name): self
    {
        if ($this->has($name)) {
            unset($this->collection[$name]);
        }

        return $this;
    }

    /**
     * @param TKey   $name
     * @param TValue $value
     *
     * @return $this
     */
    public function set($name, $value): self
    {
        parent::set($name, $value);

        return $this;
    }

    /**
     * Push item (set without key).
     *
     * @param TValue $value
     *
     * @return $this
     */
    public function push($value): self
    {
        parent::push($value);

        return $this;
    }

    /**
     * @param array<TKey, TValue> $new_collection
     *
     * @return $this
     */
    public function replace(array $new_collection): self
    {
        $this->collection = [];
        foreach ($new_collection as $key => $item) {
            $this->set($key, $item);
        }

        return $this;
    }

    /**
     * @param callable(TValue, TKey=): TValue $callable
     *
     * @return $this
     */
    public function map(callable $callable): self
    {
        if (!is_callable($callable)) {
            return $this;
        }

        $new_collection = [];
        foreach ($this->collection as $key => $item) {
            $new_collection[$key] = call_user_func($callable, $item, $key);
        }

        $this->replace($new_collection);

        return $this;
    }

    /**
     * @param callable(TValue, TKey=): bool $condition_true
     *
     * @return $this
     */
    public function filter(callable $condition_true): self
    {
        if (!is_callable($condition_true)) {
            return $this;
        }

        $new_collection = [];
        foreach ($this->collection as $key => $item) {
            $condition = $condition_true($item, $key);

            if ($condition === true) {
                $new_collection[$key] = $item;
            }
        }

        $this->replace($new_collection);

        return $this;
    }

    /**
     * @param callable(TValue, TKey=): bool $condition_true
     *
     * @return $this
     */
    public function reject(callable $condition_true): self
    {
        if (!is_callable($condition_true)) {
            return $this;
        }

        $new_collection = [];
        foreach ($this->collection as $key => $item) {
            $condition = $condition_true($item, $key);

            if ($condition === false) {
                $new_collection[$key] = $item;
            }
        }

        $this->replace($new_collection);

        return $this;
    }

    /**
     * @return $this
     */
    public function reverse(): self
    {
        return $this->replace(array_reverse($this->collection));
    }

    /**
     * @return $this
     */
    public function sort(): self
    {
        asort($this->collection);

        return $this;
    }

    /**
     * @return $this
     */
    public function sortDesc(): self
    {
        arsort($this->collection);

        return $this;
    }

    /**
     * @return $this
     */
    public function sortBy(callable $callable): self
    {
        uasort($this->collection, $callable);

        return $this;
    }

    /**
     * @return $this
     */
    public function sortByDecs(callable $callable): self
    {
        return $this->sortBy($callable)->reverse();
    }

    /**
     * @return $this
     */
    public function sortKey(): self
    {
        ksort($this->collection);

        return $this;
    }

    /**
     * @return $this
     */
    public function sortKeyDesc(): self
    {
        krsort($this->collection);

        return $this;
    }

    /**
     * @return Collection<TKey, TValue>
     */
    public function clone(): Collection
    {
        return clone $this;
    }

    /**
     * @return $this
     */
    public function chunk(int $lenght, bool $preserve_keys = true): self
    {
        $this->collection = array_chunk($this->collection, $lenght, $preserve_keys);

        return $this;
    }

    /**
     * @return $this
     */
    public function split(int $count, bool $preserve_keys = true): self
    {
        $lenght = (int) ceil($this->lenght() / $count);

        return $this->chunk($lenght);
    }

    /**
     * @param TKey[] $excepts
     *
     * @return $this
     */
    public function except(array $excepts): self
    {
        /* @phpstan-ignore-next-line */
        $this->filter(fn ($item, $key) => !in_array($key, $excepts));

        return $this;
    }

    /**
     * @param TKey[] $only
     *
     * @return $this
     */
    public function only(array $only): self
    {
        /* @phpstan-ignore-next-line */
        $this->filter(fn ($item, $key) => in_array($key, $only));

        return $this;
    }

    /**
     * @param int|float $depth
     *
     * @return $this
     */
    public function flatten($depth = INF): self
    {
        $flatten = $this->flatten_recursing($this->collection, $depth);
        $this->replace($flatten);

        return $this;
    }

    /**
     * @param array<TKey, TValue> $array
     * @param int|float           $depth
     *
     * @return array<TKey, TValue>
     */
    private function flatten_recursing(array $array, $depth = INF): array
    {
        $result = [];

        foreach ($array as $key => $item) {
            $item = $item instanceof Collection ? $item->all() : $item;

            if (!is_array($item)) {
                $result[$key] = $item;
            } else {
                $values = $depth === 1
                    ? array_values($item)
                    : $this->flatten_recursing($item, $depth - 1);

                foreach ($values as $key_dept => $value) {
                    $result[$key_dept] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * @return CollectionImmutable<TKey, TValue>
     */
    public function immutable(): CollectionImmutable
    {
        return new CollectionImmutable($this->collection);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset): void
    {
        $this->remove($offset);
    }

    /**
     * @return $this
     */
    public function shuffle(): self
    {
        $items = $this->collection;
        $keys  = $this->keys();
        shuffle($keys);
        $reordered = [];
        foreach ($keys as $key) {
            $reordered[$key] = $items[$key];
        }

        return $this->replace($reordered);
    }

    /**
     * Convert array, key and value from item (also key).
     *
     * @template TKeyItem of array-key
     * @template TValueItem
     *
     * @param callable(TValue, TKey=): array<TKeyItem, TValueItem> $callable With single key/value pair per element
     *
     * @return $this
     */
    public function assocBy(callable $callable): self
    {
        /** @var array<TKeyItem, TValueItem> */
        $new_collection = [];
        foreach ($this->collection as $key => $item) {
            $array_assoc          = $callable($item, $key);
            $key                  = array_key_first($array_assoc);
            $new_collection[$key] = $array_assoc[$key];
        }

        return $this->replace($new_collection);
    }

    /**
     * Reduce items.
     *
     * @param callable(TValue, TValue): TValue $callable
     * @param TValue|null                      $carry
     *
     * @return TValue|null
     */
    public function reduse(callable $callable, $carry = null)
    {
        foreach ($this->collection as $item) {
            $carry = $callable($carry, $item);
        }

        return $carry;
    }

    /**
     * @return $this
     */
    public function take(int $limit): self
    {
        if ($limit < 0) {
            return $this->replace(
                array_slice($this->collection, $limit, abs($limit))
            );
        }

        return $this->replace(
            array_slice($this->collection, 0, $limit)
        );
    }

    /**
     * @param array<TKey, TValue> $collection
     *
     * @return $this
     */
    public function diff($collection)
    {
        return $this->replace(
            array_diff($this->collection, $collection)
        );
    }

    /**
     * @param array<TKey, TValue> $collection
     *
     * @return $this
     */
    public function diffKeys($collection)
    {
        return $this->replace(
            array_diff_key($this->collection, $collection)
        );
    }

    /**
     * @param array<TKey, TValue> $collection
     *
     * @return $this
     */
    public function diffAssoc($collection)
    {
        return $this->replace(
            array_diff_assoc($this->collection, $collection)
        );
    }

    /**
     * @param array<TKey, TValue> $collection
     *
     * @return $this
     */
    public function complement($collection)
    {
        return $this->replace(
            array_diff($collection, $this->collection)
        );
    }

    /**
     * @param array<TKey, TValue> $collection
     *
     * @return $this
     */
    public function complementKeys($collection)
    {
        return $this->replace(
            array_diff_key($collection, $this->collection)
        );
    }

    /**
     * @param array<TKey, TValue> $collection
     *
     * @return $this
     */
    public function complementAssoc($collection)
    {
        return $this->replace(
            array_diff_assoc($collection, $this->collection)
        );
    }

    /**
     * Filter where using operator.
     *
     * @param TKey   $key
     * @param TValue $value
     *
     * @return $this
     */
    public function where($key, string $operator, $value): self
    {
        if ('=' === $operator || '==' === $operator) {
            return $this->filter(fn ($TValue) => array_key_exists($key, $TValue) && $TValue[$key] == $value);
        }
        if ('===' === $operator) {
            return $this->filter(fn ($TValue) => array_key_exists($key, $TValue) && $TValue[$key] === $value);
        }
        if ('!=' === $operator) {
            return $this->filter(fn ($TValue) => array_key_exists($key, $TValue) && $TValue[$key] != $value);
        }
        if ('!==' === $operator) {
            return $this->filter(fn ($TValue) => array_key_exists($key, $TValue) && $TValue[$key] !== $value);
        }
        if ('>' === $operator) {
            return $this->filter(fn ($TValue) => array_key_exists($key, $TValue) && $TValue[$key] > $value);
        }
        if ('>=' === $operator) {
            return $this->filter(fn ($TValue) => array_key_exists($key, $TValue) && $TValue[$key] >= $value);
        }
        if ('<' === $operator) {
            return $this->filter(fn ($TValue) => array_key_exists($key, $TValue) && $TValue[$key] < $value);
        }
        if ('<=' === $operator) {
            return $this->filter(fn ($TValue) => array_key_exists($key, $TValue) && $TValue[$key] <= $value);
        }

        return $this->replace([]);
    }

    /**
     * Filter where in range.
     *
     * @param TKey     $key
     * @param TValue[] $range
     *
     * @return $this
     */
    public function whereIn($key, $range): self
    {
        return $this->filter(fn ($TValue) => array_key_exists($key, $TValue) && in_array($TValue[$key], $range));
    }

    /**
     * Filter where not in range.
     *
     * @param TKey     $key
     * @param TValue[] $range
     *
     * @return $this
     */
    public function whereNotIn($key, $range): self
    {
        return $this->filter(fn ($TValue) => array_key_exists($key, $TValue) && false === in_array($TValue[$key], $range));
    }
}
