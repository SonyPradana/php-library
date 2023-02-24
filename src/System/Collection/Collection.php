<?php

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
     * @return self<TKey, TValue>
     */
    public function ref(AbstractCollectionImmutable $collection): self
    {
        $this->add($collection->collection);

        return $this;
    }

    /**
     * @return self<TKey, TValue>
     */
    public function clear(): self
    {
        $this->collection = [];

        return $this;
    }

    /**
     * @param array<TKey, TValue> $collection
     *
     * @return self<TKey, TValue>
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
     * @return self<TKey, TValue>
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
     * @return self<TKey, TValue>
     */
    public function set($name, $value): self
    {
        parent::set($name, $value);

        return $this;
    }

    /**
     * @param array<TKey, TValue> $new_collection
     *
     * @return self<TKey, TValue>
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
     * @return self<TKey, TValue>
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
     * @return self<TKey, TValue>
     */
    public function filter(callable $condition_true): self
    {
        if (!is_callable($condition_true)) {
            return $this;
        }

        $new_collection = [];
        foreach ($this->collection as $key => $item) {
            $call      = call_user_func($condition_true, $item, $key);
            $condition = is_bool($call) ? $call : false;

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
     * @return self<TKey, TValue>
     */
    public function reject(callable $condition_true): self
    {
        if (!is_callable($condition_true)) {
            return $this;
        }

        $new_collection = [];
        foreach ($this->collection as $key => $item) {
            $call      = call_user_func($condition_true, $item, $key);
            $condition = is_bool($call) ? $call : false;

            if ($condition === false) {
                $new_collection[$key] = $item;
            }
        }

        $this->replace($new_collection);

        return $this;
    }

    /**
     * @return self<TKey, TValue>
     */
    public function reverse(): self
    {
        return $this->replace(array_reverse($this->collection));
    }

    /**
     * @return self<TKey, TValue>
     */
    public function sort(): self
    {
        asort($this->collection);

        return $this;
    }

    /**
     * @return self<TKey, TValue>
     */
    public function sortDesc(): self
    {
        arsort($this->collection);

        return $this;
    }

    /**
     * @return self<TKey, TValue>
     */
    public function sortBy(callable $callable): self
    {
        uasort($this->collection, $callable);

        return $this;
    }

    /**
     * @return self<TKey, TValue>
     */
    public function sortByDecs(callable $callable): self
    {
        return $this->sortBy($callable)->reverse();
    }

    /**
     * @return self<TKey, TValue>
     */
    public function sortKey(): self
    {
        ksort($this->collection);

        return $this;
    }

    /**
     * @return self<TKey, TValue>
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
        return new Collection($this->collection);
    }

    /**
     * @return self<TKey, TValue>
     */
    public function chunk(int $lenght, bool $preserve_keys = true): self
    {
        $this->collection = array_chunk($this->collection, $lenght, $preserve_keys);

        return $this;
    }

    /**
     * @return self<TKey, TValue>
     */
    public function split(int $count, bool $preserve_keys = true): self
    {
        $lenght = (int) ceil($this->lenght() / $count);

        return $this->chunk($lenght);
    }

    /**
     * @param TKey[] $excepts
     *
     * @return self<TKey, TValue>
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
     * @return self<TKey, TValue>
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
     * @return self<TKey, TValue>
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
     * @return self<TKey, TValue>
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
}
