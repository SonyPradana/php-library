<?php

namespace System\Router;

use ArrayAccess;

class Route implements ArrayAccess
{
    private $route;
    private $prefix_name;

    public function __construct(array $route)
    {
        $this->prefix_name = Router::$group['as'] ?? '';
        $route['name']     = $this->prefix_name;
        $this->route       = $route;
    }

    public function __call($name, $arguments)
    {
        if ($name === 'route') {
            return $this->route;
        }
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
     * @param array $route Route class-name
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

    // ArrayAccess ---------------------------------------------

    /**
     * Assigns a value to the specified offset.
     *
     * @param string $offset the offset to assign the value to
     * @param string $value  the value to set
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
     *
     * @return string|null Can return all value types
     */
    public function offsetGet($offset)
    {
        return isset($this->route[$offset]) ? $this->route[$offset] : null;
    }
}
