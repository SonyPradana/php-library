<?php

declare(strict_types=1);

namespace System\Router;

use ArrayAccess;

/**
 * @implements \ArrayAccess<string, mixed>
 */
class Route implements \ArrayAccess
{
    /** @var array<string, mixed> */
    private array $route;
    private string $prefix_name;

    /**
     * @param array<string, mixed> $route
     */
    public function __construct(array $route)
    {
        $this->prefix_name = Router::$group['as'] ?? '';
        $route['name'] ??= '';
        $route['name'] = $this->prefix_name . $route['name'];
        $this->route   = $route;
    }

    /**
     * @param string   $name
     * @param string[] $arguments
     *
     * @return array<string, mixed>
     */
    public function __call($name, $arguments)
    {
        if ($name === 'route') {
            return $this->route;
        }

        throw new \Exception("Route {$name} not registered.");
    }

    /**
     * Set Route name.
     *
     * @param string $name Route name (uniq)
     *
     * @return self
     */
    public function name(string $name)
    {
        $this->route['name'] = $this->prefix_name . $name;

        return $this;
    }

    /**
     * Add middleware this route.
     *
     * @param class-string[] $middlewares Route class-name
     *
     * @return self
     */
    public function middleware($middlewares)
    {
        foreach ($middlewares as $middleware) {
            $this->route['middleware'][] = $middleware;
        }

        return $this;
    }

    /**
     * Costume url match pattern for this route.
     *
     * @param array<string, string> $patterns
     */
    public function where(array $patterns): self
    {
        $this->route['patterns'] = $patterns;

        return $this;
    }

    // ArrayAccess ---------------------------------------------

    /**
     * Assigns a value to the specified offset.
     *
     * @param string $offset the offset to assign the value to
     * @param mixed  $value  the value to set
     */
    public function offsetSet($offset, $value): void
    {
        $this->route[$offset] = $value;
    }

    /**
     * Whether or not an offset exists.
     * This method is executed when using isset() or empty().
     *
     * @param string $offset an offset to check for
     *
     * @return bool returns true on success or false on failure
     */
    public function offsetExists($offset): bool
    {
        return isset($this->route[$offset]);
    }

    /**
     * Unsets an offset.
     *
     * @param string $offset unsets an offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->route[$offset]);
    }

    /**
     * Returns the value at specified offset.
     *
     * @param string $offset the offset to retrieve
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset): mixed
    {
        return $this->route[$offset] ?? null;
    }
}
