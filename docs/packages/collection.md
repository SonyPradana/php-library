---
title: Collection
namespace: SonyPradana\System\Collection
description: A powerful and expressive wrapper for working with arrays of data.
source: src/System/Collection/
---

# Collection

The Collection component provides a fluent, convenient wrapper for working with arrays of data. It offers a host of methods that allow you to chain operations, leading to more readable and expressive code than when working with native arrays.

## Table of Contents

- [Creating Collections](#creating-collections)
- [Retrieving Data](#retrieving-data)
  - [all](#all)
  - [get](#get)
  - [first](#first)
  - [last](#last)
  - [firsts](#firsts)
  - [lasts](#lasts)
  - [rand](#rand)
  - [pluck](#pluck)
  - [keys](#keys)
  - [items](#items)
  - [firstKey](#firstkey)
  - [lastKey](#lastkey)
  - [toArray](#toarray)
  - [json](#json)
- [Pointer Operations](#pointer-operations)
  - [current](#current)
  - [next](#next)
  - [prev](#prev)
- [Iterating](#iterating)
  - [each](#each)
  - [map](#map)
  - [filter](#filter)
  - [reduce](#reduce)
- [Checking for Items](#checking-for-items)
  - [has](#has)
  - [contain](#contain)
  - [some](#some)
  - [every](#every)
  - [isEmpty](#isempty)
- [Counting & Aggregating](#counting--aggregating)
  - [count](#count)
  - [length](#length)
  - [countIf](#countif)
  - [countBy](#countby)
  - [sum](#sum)
  - [avg](#avg)
  - [min](#min)
  - [max](#max)
- [Debugging](#debugging)
  - [dump](#dump)
- [Immutable Collections](#immutable-collections)

---

## Creating Collections

You can create a new collection instance from any iterable, such as a plain PHP array.

```php
use SonyPradana\System\Collection\Collection;

$collection = new Collection([
    ['product' => 'Desk', 'price' => 200],
    ['product' => 'Chair', 'price' => 100],
    ['product' => 'Bookcase', 'price' => 150],
]);
```

[Back to top](#collection)

## Retrieving Data

### all

The `all` method returns the underlying array represented by the collection.

```php
$array = (new Collection([1, 2, 3]))->all();

// $array is [1, 2, 3]
```

[Back to top](#collection)

### get

The `get` method retrieves an item from the collection by its key. If the key does not exist, it returns `null`. You can also provide a second argument as a default value.

```php
$collection = new Collection(['name' => 'Desk', 'price' => 100]);

$name = $collection->get('name'); // 'Desk'
$stock = $collection->get('stock', 0); // 0
```

[Back to top](#collection)

### first

The `first` method returns the first element in the collection. You can provide a default value as the first argument.

```php
$first = (new Collection([10, 20, 30]))->first(); // 10
```

[Back to top](#collection)

### last

The `last` method returns the last element in the collection. You can provide a default value as the first argument.

```php
$last = (new Collection([10, 20, 30]))->last(); // 30
```

[Back to top](#collection)

### firsts

The `firsts` method returns a new array containing the first N elements.

```php
$firstTwo = (new Collection([10, 20, 30, 40]))->firsts(2);

// $firstTwo is [10, 20]
```

[Back to top](#collection)

### lasts

The `lasts` method returns a new array containing the last N elements.

```php
$lastTwo = (new Collection([10, 20, 30, 40]))->lasts(2);

// $lastTwo is [30, 40]
```

[Back to top](#collection)

### rand

The `rand` method returns a random element from the collection.

```php
$randomItem = (new Collection([1, 2, 3, 4, 5]))->rand();
```

[Back to top](#collection)

### pluck

The `pluck` method retrieves all of the values for a given key from a collection of arrays or objects. You can also specify a second argument to use as the key for the resulting array.

```php
$collection = new Collection([
    ['product' => 'Desk', 'price' => 200],
    ['product' => 'Chair', 'price' => 100],
]);

$prices = $collection->pluck('price'); // [200, 100]
$products = $collection->pluck('product', 'price'); // [200 => 'Desk', 100 => 'Chair']
```

[Back to top](#collection)

### keys

The `keys` method returns an array of all the collection's keys.

```php
$keys = (new Collection(['a' => 1, 'b' => 2]))->keys();

// $keys is ['a', 'b']
```

[Back to top](#collection)

### items

The `items` method returns an array of all the collection's values, re-indexed with numeric keys.

```php
$items = (new Collection(['a' => 1, 'b' => 2]))->items();

// $items is [1, 2]
```

[Back to top](#collection)

### firstKey

The `firstKey` method returns the key of the first element in the collection.

```php
$key = (new Collection(['a' => 1, 'b' => 2, 'c' => 3]))->firstKey(); // 'a'
```

[Back to top](#collection)

### lastKey

The `lastKey` method returns the key of the last element in the collection.

```php
$key = (new Collection(['a' => 1, 'b' => 2, 'c' => 3]))->lastKey(); // 'c'
```

[Back to top](#collection)

### toArray

The `toArray` method is an alias for the `all` method. It returns the collection as a plain PHP array.

```php
$array = (new Collection([1, 2, 3]))->toArray();

// $array is [1, 2, 3]
```

[Back to top](#collection)

### json

The `json` method converts the collection into a JSON string.

```php
$json = (new Collection(['name' => 'Desk', 'price' => 100]))->json();

// $json is '{"name":"Desk","price":100}'
```

[Back to top](#collection)

---

## Pointer Operations

These methods interact with the internal array pointer, similar to native PHP array functions.

### current

The `current` method returns the element at the current pointer position.

```php
$collection = new Collection([10, 20, 30]);
$current = $collection->current(); // 10
```

[Back to top](#collection)

### next

The `next` method advances the internal pointer and returns the next element.

```php
$collection = new Collection([10, 20, 30]);
$collection->next();
$current = $collection->current(); // 20
```

[Back to top](#collection)

### prev

The `prev` method moves the internal pointer back and returns the previous element.

```php
$collection = new Collection([10, 20, 30]);
$collection->next(); // pointer at 20
$collection->next(); // pointer at 30
$collection->prev();
$current = $collection->current(); // 20
```

[Back to top](#collection)

---

## Iterating

### each

The `each` method iterates over the items in the collection and passes each item to a callback. Returning `false` from the callback will stop the iteration.

```php
(new Collection([1, 2, 3]))->each(function ($item, $key) {
    // Process the item...
    if ($item > 2) {
        return false; // Stop iterating
    }
});
```

[Back to top](#collection)

### map

The `map` method iterates through the collection and passes each value to a given callback. The callback's return value will be added to a new collection.

```php
$doubled = (new Collection([1, 2, 3]))->map(function ($item) {
    return $item * 2;
});

// $doubled is a new Collection instance containing [2, 4, 6]
```

[Back to top](#collection)

### filter

The `filter` method filters the collection using a given callback, keeping only those items that pass a given truth test.

```php
$filtered = (new Collection([1, 2, 3, 4]))->filter(function ($value) {
    return $value > 2;
});

// $filtered is a new Collection instance containing [3, 4]
```

[Back to top](#collection)

### reduce

The `reduce` method reduces the collection to a single value, passing the result of each iteration into the subsequent iteration.

```php
$total = (new Collection([1, 2, 3, 4, 5]))->reduce(function ($carry, $item) {
    return $carry + $item;
}, 0);

// $total equals 15
```

[Back to top](#collection)

---

## Checking for Items

### has

The `has` method determines if a given key exists in the collection.

```php
$collection = new Collection(['name' => 'Desk', 'price' => 100]);

$collection->has('name'); // true
$collection->has('stock'); // false
```

[Back to top](#collection)

### contain

The `contain` method determines whether the collection contains a given item.

```php
$collection = new Collection([1, 2, 3]);

$collection->contain(2); // true
$collection->contain(4); // false
```

[Back to top](#collection)

### some

The `some` method determines if at least one element in the collection passes the given truth test.

```php
$collection = new Collection([1, 2, 3, 4]);

$collection->some(function ($value, $key) {
    return $value > 3;
}); // true
```

[Back to top](#collection)

### every

The `every` method verifies that all elements of a collection pass a given truth test.

```php
$collection = new Collection([2, 4, 6, 8]);

$collection->every(function ($value, $key) {
    return $value % 2 === 0;
}); // true
```

[Back to top](#collection)

### isEmpty

The `isEmpty` method returns `true` if the collection is empty; otherwise, `false` is returned.

```php
(new Collection([]))->isEmpty(); // true
```

[Back to top](#collection)

---

## Counting & Aggregating

### count

The `count` method returns the total number of items in the collection.

```php
$count = (new Collection([1, 2, 3, 4]))->count(); // 4
```

[Back to top](#collection)

### length

The `length` method is an alias for the `count` method.

```php
$length = (new Collection([1, 2, 3, 4]))->length(); // 4
```

[Back to top](#collection)

### countIf

The `countIf` method counts the number of items in the collection that pass a given truth test.

```php
$count = (new Collection([1, 2, 3, 4]))->countIf(function ($item) {
    return $item > 2;
}); // 2
```

[Back to top](#collection)

### countBy

The `countBy` method counts the occurrences of values in the collection. It returns an array of values with their counts.

```php
$counted = (new Collection(['a', 'b', 'a', 'c', 'a']))->countBy();

// $counted is ['a' => 3, 'b' => 1, 'c' => 1]
```

[Back to top](#collection)

### sum

The `sum` method returns the sum of all items in the collection.

```php
$sum = (new Collection([1, 2, 3, 4, 5]))->sum(); // 15
```

[Back to top](#collection)

### avg

The `avg` method returns the average value of all items in the collection.

```php
$average = (new Collection([1, 2, 3, 4, 5]))->avg(); // 3
```

[Back to top](#collection)

### min

The `min` method returns the minimum value in the collection. For a collection of arrays, pass a key to specify which value to compare.

```php
(new Collection([10, 5, 20]))->min(); // 5
```

[Back to top](#collection)

### max

The `max` method returns the maximum value in the collection. For a collection of arrays, pass a key to specify which value to compare.

```php
(new Collection([10, 5, 20]))->max(); // 20
```

[Back to top](#collection)

---

## Debugging

### dump

The `dump` method dumps the collection's underlying array and continues execution. This is useful for debugging.

```php
(new Collection([1, 2, 3]))->dump();
```

[Back to top](#collection)

---

## Immutable Collections

For situations where you need to ensure that your original data is never modified, the library may also provide an `ImmutableCollection` class. Any method that would typically modify the collection will return a new, modified instance, leaving the original untouched.

*(Note: Check the source or specific API documentation for availability of `ImmutableCollection`)*

[Back to top](#collection)