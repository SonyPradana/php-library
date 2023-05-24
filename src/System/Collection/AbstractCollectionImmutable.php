<?php

namespace System\Collection;

use System\Collection\Interfaces\CollectionInterface;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @implements CollectionInterface<TKey, TValue>
 */
abstract class AbstractCollectionImmutable implements CollectionInterface
{
    /**
     * @var array<TKey, TValue>
     */
    protected array $collection = [];

    /**
     * @param iterable<TKey, TValue> $collection
     */
    public function __construct($collection)
    {
        foreach ($collection as $key => $item) {
            $this->set($key, $item);
        }
    }

    /**
     * @param TKey $name
     *
     * @return TValue|null
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @return array<TKey, TValue>
     */
    public function all(): array
    {
        return $this->collection;
    }

    public function toArray(): array
    {
        return $this->collection;
    }

    /**
     * @template TGetDefault
     *
     * @param TKey             $name
     * @param TGetDefault|null $default
     *
     * @return TValue|TGetDefault|null
     */
    public function get($name, $default = null)
    {
        return $this->collection[$name] ?? $default;
    }

    /**
     * @param TKey   $name
     * @param TValue $value
     *
     * @return $this
     */
    protected function set($name, $value): self
    {
        $this->collection[$name] = $value;

        return $this;
    }

    /**
     * @param TKey $key
     */
    public function has($key): bool
    {
        return array_key_exists($key, $this->collection);
    }

    /**
     * @param TValue $item
     */
    public function contain($item, bool $strict = false): bool
    {
        return in_array($item, $this->collection, $strict);
    }

    /**
     * @return TKey[]
     */
    public function keys(): array
    {
        return array_keys($this->collection);
    }

    /**
     * @return TValue[]
     */
    public function items(): array
    {
        return array_values($this->collection);
    }

    public function count(): int
    {
        return count($this->collection);
    }

    /**
     * @param callable(TValue, TKey=): bool $condition
     */
    public function countIf(callable $condition): int
    {
        $count = 0;
        foreach ($this->collection as $key => $item) {
            $do_somethink = call_user_func($condition, $item, $key);

            $count += $do_somethink === true ? 1 : 0;
        }

        return $count;
    }

    /**
     * @return array<TKey, int>
     */
    public function countBy(): array
    {
        return array_count_values($this->collection);
    }

    /**
     * @param callable(TValue, TKey=): bool $callable
     *
     * @return $this
     */
    public function each(callable $callable): self
    {
        if (!is_callable($callable)) {
            return $this;
        }

        foreach ($this->collection as $key => $item) {
            $do_somethink = call_user_func($callable, $item, $key);

            // stop looping if callable returning false
            if ($do_somethink === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function dumb(): self
    {
        var_dump($this->collection);

        return $this;
    }

    /**
     * @param callable(TValue, TKey=): bool $condition
     */
    public function some(callable $condition): bool
    {
        foreach ($this->collection as $key => $item) {
            $call = call_user_func($condition, $item, $key);

            if ($call === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param callable(TValue, TKey=): bool $condition
     */
    public function every(callable $condition): bool
    {
        foreach ($this->collection as $key => $item) {
            $call = call_user_func($condition, $item, $key);

            if ($call === false) {
                return false;
            }
        }

        return true;
    }

    public function json(): string
    {
        return json_encode($this->collection);
    }

    /**
     * @template TGetDefault
     *
     * @param TGetDefault|null $default
     *
     * @return TValue|TGetDefault|null
     */
    public function first($default = null)
    {
        $key = array_key_first($this->collection) ?? 0;

        return $this->collection[$key] ?? $default;
    }

    /**
     * @template TGetDefault
     *
     * @param TGetDefault|null $default
     *
     * @return TValue|TGetDefault|null
     */
    public function last($default = null)
    {
        $key = array_key_last($this->collection);

        return $this->collection[$key] ?? $default;
    }

    /**
     * @return TValue
     */
    public function current()
    {
        return current($this->collection);
    }

    /**
     * @return TValue
     */
    public function next()
    {
        return next($this->collection);
    }

    /**
     * @return TValue
     */
    public function prev()
    {
        return prev($this->collection);
    }

    /**
     * @return TValue
     */
    public function rand()
    {
        $rand = array_rand($this->collection);

        return $this->get($rand);
    }

    public function isEmpty(): bool
    {
        return empty($this->collection);
    }

    public function lenght(): int
    {
        return count($this->collection);
    }

    public function sum(): int
    {
        return array_sum($this->collection);
    }

    public function avg(): int
    {
        return $this->sum() / $this->count();
    }

    // array able

   /**
    * @param TKey $offset
    */
   public function offsetExists($offset): bool
   {
       return $this->has($offset);
   }

   /**
    * @param TKey $offset
    *
    * @return TValue|null
    */
   #[\ReturnTypeWillChange]
   public function offsetGet($offset)
   {
       return $this->__get($offset);
   }

    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset): void
    {
    }

    /**
     * @return \Traversable<TKey, TValue>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->all());
    }

    public function __clone()
    {
        $this->collection = $this->deepClone($this->collection);
    }

    /**
     * @param array<TKey, TValue> $collection
     *
     * @return array<TKey, TValue>
     */
    protected function deepClone($collection)
    {
        $clone = [];
        foreach ($collection as $key => $value) {
            if (is_array($value)) {
                $clone[$key] = $this->deepClone($value);
                continue;
            }

            if (is_object($value)) {
                $clone[$key] = clone $value;
                continue;
            }

            $clone[$key] = $value;
        }

        return $clone;
    }
}
