<?php

namespace System\Collection;

use System\Collection\Interfaces\CollectionInterface;

/**
 * @template T
 *
 * @implements CollectionInterface<T>
 */
abstract class AbstractCollectionImmutable implements CollectionInterface
{
    /**
     * @var array<array-key, T>
     */
    protected array $collection = [];

    /**
     * @param iterable<array-key, T> $collection
     */
    public function __construct($collection)
    {
        foreach ($collection as $key => $item) {
            $this->set($key, $item);
        }
    }

    /**
     * @param array-key $name
     *
     * @return T|null
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @return array<array-key, T>
     */
    public function all(): array
    {
        return $this->collection;
    }

    /**
     * @template TGetDefault
     *
     * @param array-key        $name
     * @param TGetDefault|null $default
     *
     * @return T|TGetDefault|null
     */
    public function get($name, $default = null)
    {
        return $this->collection[$name] ?? $default;
    }

    /**
     * @param array-key $name
     * @param T         $value
     *
     * @return self<T>
     */
    protected function set($name, $value): self
    {
        $this->collection[$name] = $value;

        return $this;
    }

    /**
     * @param array-key $key
     */
    public function has($key): bool
    {
        return array_key_exists($key, $this->collection);
    }

    /**
     * @param T $item
     */
    public function contain($item): bool
    {
        return in_array($item, $this->collection);
    }

    /**
     * @return array-key[]
     */
    public function keys(): array
    {
        return array_keys($this->collection);
    }

    /**
     * @return T[]
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
     * @param callable(T, array-key=): bool $condition
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
     * @return array<array-key, int>
     */
    public function countBy(): array
    {
        return array_count_values($this->collection);
    }

    /**
     * @param callable(T, array-key=): bool $callable
     *
     * @return self<T>
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
     * @return self<T>
     */
    public function dumb(): self
    {
        var_dump($this->collection);

        return $this;
    }

    /**
     * @param callable(T, array-key=): bool $condition
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
     * @param callable(T, array-key=): bool $condition
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
     * @return T|TGetDefault|null
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
     * @return T|TGetDefault|null
     */
    public function last($default = null)
    {
        $key = array_key_last($this->collection);

        return $this->collection[$key] ?? $default;
    }

    /**
     * @return T
     */
    public function current()
    {
        return current($this->collection);
    }

    /**
     * @return T
     */
    public function next()
    {
        return next($this->collection);
    }

    /**
     * @return T
     */
    public function prev()
    {
        return prev($this->collection);
    }

    /**
     * @return T
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
