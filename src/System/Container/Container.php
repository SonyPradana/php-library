<?php

declare(strict_types=1);

namespace System\Container;

use DI\Container as DIContainer;

/**
 * @implements \ArrayAccess<string|class-string<mixed>, mixed>
 */
class Container extends DIContainer implements \ArrayAccess
{
    /**
     * Registed aliases entry container.
     *
     * @var array<string, string>
     */
    protected array $aliases = [];

    /**
     * {@inheritDoc}
     */
    public function get(string $id): mixed
    {
        $id = $this->getAlias($id);

        return parent::get($id);
    }

    /**
     * {@inheritDoc}
     *
     * @param array<array-key, mixed> $parameters Optional parameters to use to build the entry. Use this to force
     *                                            specific parameters to specific values. Parameters not defined in this
     *                                            array will be resolved using the container.
     */
    public function make(string $name, array $parameters = []): mixed
    {
        $name = $this->getAlias($name);

        return parent::make($name, $parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $id): bool
    {
        $id = $this->getAlias($id);

        return parent::has($id);
    }

    /**
     * Set entry alias conntainer.
     */
    public function alias(string $abstract, string $alias): void
    {
        if ($abstract === $alias) {
            throw new \Exception("{$abstract} is aliased to itself.");
        }

        $this->aliases[$alias] = $abstract;
    }

    /**
     * Get alias for an abstract if available.
     */
    public function getAlias(string $abstract): string
    {
        return array_key_exists($abstract, $this->aliases)
            ? $this->getAlias($this->aliases[$abstract])
            : $abstract;
    }

    /**
     * Flush container.
     */
    public function flush(): void
    {
        $this->aliases              = [];
        $this->resolvedEntries      = [];
        $this->entriesBeingResolved = [];
    }

    /**
     * Offest exist check.
     *
     * @param string $offset
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Get the value.
     *
     * @param string|class-string<mixed> $offset entry name or a class name
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->make($offset);
    }

    /**
     * Set the value.
     *
     * @param string $offset
     * @param mixed  $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * Unset the value.
     *
     * @param string $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->resolvedEntries[$offset]);
    }
}
