---
title: Cache
namespace: SonyPradana\System\Cache
description: An overview of the Cache component for storing temporary data.
source: src/System/Cache/
---

# Cache

The SonyPradana Cache component provides an expressive, unified API for various caching backends. It allows you to easily store and retrieve temporary data, improving your application's performance.

## Table of Contents

- [Basic Usage](#basic-usage)
  - [Storing Items](#storing-items)
  - [Retrieving Items](#retrieving-items)
  - [Checking for Item Existence](#checking-for-item-existence)
  - [Retrieve & Store (Remember)](#retrieve--store-remember)
  - [Removing Items](#removing-items)
- [Advanced Usage](#advanced-usage)
  - [The Cache Manager](#the-cache-manager)
  - [Available Drivers](#available-drivers)
  - [Creating Custom Drivers](#creating-custom-drivers)

---

## Basic Usage

The `Cache` facade provides convenient access to the default cache driver. For most use cases, this is all you will need.

### Storing Items

You can store items in the cache using the `put()` method. This method accepts three arguments: a string `$key`, the `$value` to store, and an integer `$seconds` representing the expiration time.

For example, to store a user's profile for 10 minutes (600 seconds):

```php
use SonyPradana\System\Support\Facades\Cache;

Cache::put('user:1:profile', $profile, 600);
```

[Back to top](#cache)

### Retrieving Items

The `get()` method is used to retrieve an item from the cache by its key. If the item does not exist in the cache, `null` will be returned. You may also pass a second argument to specify a custom default value to return if the item is missing.

```php
$profile = Cache::get('user:1:profile');

$profile = Cache::get('user:2:profile', $defaultProfile);
```

[Back to top](#cache)

### Checking for Item Existence

You can use the `has()` method to determine if an item exists in the cache. This method will return `true` if the item exists, and `false` if it does not.

```php
if (Cache::has('user:1:profile')) {
    // Logic to execute if the profile exists...
}
```

[Back to top](#cache)

### Retrieve & Store (Remember)

The `remember` method offers a convenient way to retrieve an item from the cache or store it if it doesn't exist. It takes a `$key`, an expiration time in `$seconds`, and a `Closure` as arguments. If the key exists, its value is returned. Otherwise, the `Closure` is executed, and its result is stored in the cache for the specified duration.

```php
$profile = Cache::remember('user:1:profile', 600, function () {
    return DB::table('users')->where('id', 1)->first();
});
```

[Back to top](#cache)

### Removing Items

You can remove an item from the cache using the `forget()` method, which accepts the item's key as its only argument.

```php
Cache::forget('user:1:profile');
```

[Back to top](#cache)

---

## Advanced Usage

### The Cache Manager

Behind the scenes, the `Cache` facade resolves an instance of the `CacheManager` class from the service container. The manager is responsible for creating and managing all the various cache drivers for the application.

You can also use the manager to access different cache stores that are not your default driver.

```php
// Get the file cache store instance
$fileCache = Cache::store('file');

// Get the redis cache store instance
$redisCache = Cache::store('redis');

$fileCache->put('foo', 'bar', 60);
```

[Back to top](#cache)

### Available Drivers

SonyPradana is designed to ship with several common cache drivers, including:

- **file**: Stores cache items in the filesystem.
- **database**: Uses a database table to store cache items.
- **array**: A non-persistent cache for the current request, primarily used during testing.

[Back to top](#cache)

### Creating Custom Drivers

For maximum flexibility, SonyPradana allows you to define your own custom cache drivers.

#### 1. Implement the Interface

First, you need to create a class that implements the `SonyPradana\System\Cache\Contracts\Store` interface. This interface defines methods such as `get`, `put`, `has`, `forget`, and `flush`.

```php
use SonyPradana\System\Cache\CacheInterface;

class MongoCacheStore implements CacheInterface
{
    // Your implementation of all interface methods...

    public function get($key)
    {
        // ...
    }

    public function put($key, $value, $seconds)
    {
        // ...
    }

    // etc...
}
```

#### 2. Register the Driver

Once your custom driver is complete, you need to register it with the `CacheManager`. You can do this by using the `extend` method on the `Cache` facade, typically within a `boot` method of a `ServiceProvider`.

```php
use SonyPradana\System\Support\Facades\Cache;

public function boot()
{
    Cache::setDriver('mongo', MongoCacheStore($app['mongo.connection']));
}
```

After registering the driver, you can use `mongo` as a cache store in your application.

[Back to top](#cache)
