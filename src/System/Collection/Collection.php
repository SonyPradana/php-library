<?php

namespace System\Collection;

class Collection extends AbstractCollectionImmutable
{

  public function __set($name, $value)
  {
    return $this->set($name, $value);
  }

  public function clear()
  {
    $this->collection = [];
    return $this;
  }

  public function add(array $params)
  {
    foreach ($params as $key => $item) {
      $this->set($key, $item);
    }
    return $this;
  }

  public function remove(string $name)
  {
    if ($this->has($name)) {
      unset($this->collection[$name]);
    }

    return $this;
  }

  public function set(string $name, $value)
  {
    parent::set($name, $value);

    return $this;
  }

  public function replace(array $new_collection)
  {
    $this->collection = [];
    foreach ($new_collection as $key => $item) {
      $this->set($key, $item);
    }

    return $this;
  }

  public function map(callable $callable)
  {
    if (! is_callable($callable)) {
      return $this;
    }

    $new_collection = [];
    foreach ($this->collection as $key => $item) {
      $new_collection[$key] = call_user_func($callable, $item, $key);
    }

    $this->replace($new_collection);

    return $this;
  }

  public function filter(callable $condition_true)
  {
    if (! is_callable($condition_true)) {
      return $this;
    }

    $new_collection = [];
    foreach ($this->collection as $key => $item) {
      $call = call_user_func($condition_true, $item, $key);
      $condition = is_bool($call) ? $call : false;

      if ($condition === true) {
        $new_collection[$key] = $item;
      }
    }

    $this->replace($new_collection);

    return $this;
  }

  public function reject(callable $condition_true)
  {
    if (! is_callable($condition_true)) {
      return $this;
    }

    $new_collection = [];
    foreach ($this->collection as $key => $item) {
      $call = call_user_func($condition_true, $item, $key);
      $condition = is_bool($call) ? $call : false;

      if ($condition === false) {
        $new_collection[$key] = $item;
      }
    }

    $this->replace($new_collection);

    return $this;
  }

  public function reverse()
  {
    return $this->replace(array_reverse($this->collection));
  }

  public function sort()
  {
    asort($this->collection);

    return $this;
  }

  public function sortDesc()
  {
    arsort($this->collection);

    return $this;
  }

  public function sortBy(callable $callable)
  {
    uasort($this->collection, $callable);

    return $this;
  }

  public function sortByDecs(callable $callable)
  {
    return $this->sortBy($callable)->reverse();
  }

  public function sortKey()
  {
    ksort($this->collection);

    return $this;
  }

  public function sortKeyDesc()
  {
    krsort($this->collection);

    return $this;
  }

  public function clone()
  {
    return new Collection($this->collection);
  }

  public function chunk(int $lenght, bool $preserve_keys = true): self
  {
    $this->collection = array_chunk($this->collection, $lenght, $preserve_keys);

    return $this;
  }

  public function split(int $count, bool $preserve_keys = true): self
  {
    $lenght = ceil($this->lenght() / $count);
    return $this->chunk($lenght);
  }

  public function except(array $excepts)
  {
    $this->filter(fn ($item, $key) => ! in_array($key, $excepts));

    return $this;
  }

  public function only(array $only)
  {
    $this->filter(fn ($item, $key) => in_array($key, $only));

    return $this;
  }

  public function flatten($depth = INF): self
  {
    $flatten = $this->flatten_recursing($this->collection, $depth);
    $this->replace($flatten);

    return $this;
  }

  private function flatten_recursing(array $array, $depth = INF): array
  {
    $result = [];

    foreach ($array as $key => $item) {
      $item = $item instanceof Collection ? $item->all() : $item;

      if (! is_array($item)) {
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

  public function immutable(): CollectionImmutable
  {
    return new CollectionImmutable($this->collection);
  }

}
