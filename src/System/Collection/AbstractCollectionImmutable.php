<?php

namespace System\Collection;

abstract class AbstractCollectionImmutable implements \ArrayAccess, \IteratorAggregate
{
    protected array $collection = [];

    public function __construct(array $collection)
    {
        foreach ($collection as $key => $item) {
            $this->set($key, $item);
        }
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function all(): array
    {
        return $this->collection;
    }

    public function get(string $name, $default = null)
    {
        return $this->collection[$name] ?? $default;
    }

    protected function set(string $name, $value)
    {
        $this->collection[$name] = $value;

        return $this;
    }

    public function has(string $key)
    {
        return array_key_exists($key, $this->collection);
    }

    public function contain($item)
    {
        return in_array($item, $this->collection);
    }

    public function keys(): array
    {
        return array_keys($this->collection);
    }

    public function items(): array
    {
        return array_values($this->collection);
    }

    public function count(): int
    {
        return count($this->collection);
    }

    public function countIf(callable $condition): int
    {
        $count = 0;
        foreach ($this->collection as $key => $item) {
            $do_somethink = call_user_func($condition, $item, $key);

            $count += $do_somethink === true ? 1 : 0;
        }

        return $count;
    }

    public function countBy(): array
    {
        return array_count_values($this->collection);
    }

    public function each(callable $callable)
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

    public function dumb()
    {
        var_dump($this->collection);

        return $this;
    }

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

    public function first($default = null)
    {
        $key = array_key_first($this->collection) ?? 0;

        return $this->collection[$key] ?? $default;
    }

    public function last($default = null)
    {
        $key = array_key_last($this->collection);

        return $this->collection[$key] ?? $default;
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
