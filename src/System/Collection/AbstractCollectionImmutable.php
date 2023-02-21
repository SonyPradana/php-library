<?php

namespace System\Collection;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @implements \ArrayAccess<TKey, TValue>
 */
abstract class AbstractCollectionImmutable implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * @var array<TKey, TValue>
     */
    protected array $collection = [];

    /**
     * @param iterable<TKey, TValue>|null $collection
     */
    public function __construct(array $collection)
    {
        foreach ($collection as $key => $item) {
            $this->set($key, $item);
        }
    }

    /**
     * @template TGetDefault
     *
     * @param TKey $name
     *
     * @return TValue|TGetDefault
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

    /**
     * @template TGetDefault
     *
     * @param TKey             $name
     * @param TGetDefault|null $default
     *
     * @return TValue|TGetDefault
     */
    public function get($name, $default = null)
    {
        return $this->collection[$name] ?? $default;
    }

    /**
     * @param TKey   $name
     * @param TValue $value
     */
    protected function set($name, $value)
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
    public function contain($item): bool
    {
        return in_array($item, $this->collection);
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
     * @param callable(TValue, TKey) $condition
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
     * @return array<Tkey, int>
     */
    public function countBy(): array
    {
        return array_count_values($this->collection);
    }

    /**
     * @param callable(TValue, TKey) $condition
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

    public function dumb(): self
    {
        var_dump($this->collection);

        return $this;
    }

    /**
     * @param callable(TValue, TKey) $condition
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
     * @param callable(TValue, TKey) $condition
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
     * @return KValue
     */
    public function current()
    {
        return current($this->collection);
    }

    /**
     * @return KValue
     */
    public function next()
    {
        return next($this->collection);
    }

    /**
     * @return KValue
     */
    public function prev()
    {
        return prev($this->collection);
    }

    /**
     * @return KValue
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
}
