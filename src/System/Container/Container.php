<?php

declare(strict_types=1);

namespace System\Container;

use System\Container\Attribute\Inject;
use System\Container\Exceptions\AliasException;
use System\Container\Exceptions\BindingResolutionException;
use System\Container\Exceptions\CircularAliasException;
use System\Container\Exceptions\EntryNotFoundException;

/**
 * @implements \ArrayAccess<string|class-string<mixed>, mixed>
 */
class Container implements \ArrayAccess
{
    /**
     * The container's bindings.
     *
     * @var array<string, array{concrete: \Closure, shared: bool}>
     */
    protected array $bindings = [];

    /**
     * The container's shared instances (singleton cache).
     *
     * @var array<string, mixed>
     */
    protected array $instances = [];

    /**
     * The registered type aliases.
     *
     * @var array<string, string>
     */
    protected array $aliases = [];

    /**
     * The dependency resolver instance.
     */
    private ?Resolver $resolver = null;

    /**
     * The callable invoker instance.
     */
    private ?Invoker $invoker = null;

    /**
     * The reflection cache instance.
     */
    private ?ReflectionCache $reflectionCache = null;

    /**
     * The dependency injector instance.
     */
    private ?Injector $injector = null;

    /**
     * The parameter override stack.
     *
     * @var list<array<mixed>>
     */
    protected array $with = [];

    /**
     * Resolve and return an entry from the container.
     *
     * @throws EntryNotFoundException
     * @throws BindingResolutionException
     */
    public function get(string $name): mixed
    {
        if (false === $this->has($name)
        && false === class_exists($name)
        && false === interface_exists($name)
        ) {
            throw new EntryNotFoundException($name);
        }

        return $this->resolve($name, [], true);
    }

    /**
     * Resolve a new instance from the container.
     *
     * @param string|class-string      $name
     * @param array<int|string, mixed> $parameters
     *
     * @throws BindingResolutionException
     */
    public function make(string $name, array $parameters = []): mixed
    {
        return $this->resolve($name, $parameters, false);
    }

    /**
     * Define an object or a value in the container.
     */
    public function set(string $name, mixed $value): void
    {
        // If the value is a Closure,
        // it's a factory for a shared instance.
        if ($value instanceof \Closure) {
            $this->bind($name, $value, true);

            return;
        }

        $name = $this->getAlias($name);

        // Store the value directly as a resolved.
        $this->instances[$name] = $value;
        $this->bindings[$name]  = [
            'concrete' => fn () => $this->instances[$name],
            'shared'   => true,
        ];
    }

    /**
     * Register a binding with the container.
     */
    public function bind(string $abstract, \Closure|string|null $concrete = null, bool $shared = false): void
    {
        $abstract = $this->getAlias($abstract);

        $concrete ??= $abstract;

        // If the concrete is not a Closure, we will make it one.
        if (false === $concrete instanceof \Closure) {
            $concrete = $this->getClosure($abstract, $concrete);
        }

        $this->bindings[$abstract] = compact('concrete', 'shared');
    }

    /**
     * Determine if a given identifier has a value or binding.
     */
    public function has(string $id): bool
    {
        return $this->bound($id) || class_exists($id) || interface_exists($id);
    }

    /**
     * Determine if the given abstract type has been bound.
     */
    public function bound(string $abstract): bool
    {
        $abstract = $this->getAlias($abstract);

        return isset($this->bindings[$abstract])
               || isset($this->instances[$abstract])
               || isset($this->aliases[$abstract]);
    }

    /**
     * Alias a type to a different name.
     */
    public function alias(string $abstract, string $alias): void
    {
        if ($abstract === $alias) {
            throw new AliasException($abstract);
        }

        $this->aliases[$alias] = $abstract;
    }

    /**
     * Get the alias for an abstract if available.
     */
    public function getAlias(string $abstract): string
    {
        return $this->resolveAlias($abstract, []);
    }

    /**
     * Instantiate a concrete instance of the given type.
     *
     * @param array<int|string, mixed> $parameters
     *
     * @throws BindingResolutionException
     */
    public function build(string|\Closure $concrete, array $parameters = []): mixed
    {
        // If the concrete is actually a Closure,
        // just execute it and return the result.
        if ($concrete instanceof \Closure) {
            return $concrete($this, $parameters);
        }

        return $this->getResolver()->resolveClass($concrete, $parameters);
    }

    /**
     * Call the given callable and inject its dependencies.
     *
     * @param callable|object|array<string>|string     $callable   $callable
     * @param array<int|string<string, string>, mixed> $parameters
     *
     * @throws BindingResolutionException
     */
    public function call(callable|object|array|string $callable, array $parameters = []): mixed
    {
        return $this->getInvoker()->call($callable, $parameters);
    }

    /**
     * Inject dependencies on an existing instance.
     *
     * @param object $instance Object to perform injection upon
     */
    public function injectOn(object $instance): object
    {
        return $this->getInjector()->inject($instance);
    }

    /**
     * @return \ReflectionClass<object>
     *
     * @internal
     */
    public function getReflectionClass(string $class): \ReflectionClass
    {
        return $this->getReflectionCache()->getReflectionClass($class, function () use ($class) {
            if (false === class_exists($class) && false === interface_exists($class)) {
                throw new \ReflectionException("Class {$class} does not exist");
            }

            return new \ReflectionClass($class);
        });
    }

    /**
     * @internal
     */
    public function getReflectionMethod(string|object $class, string $method): \ReflectionMethod
    {
        $className = is_object($class) ? $class::class : $class;

        return $this->getReflectionCache()->getReflectionMethod($className, $method, fn () => new \ReflectionMethod($class, $method));
    }

    /**
     * @return ?array<\ReflectionParameter>
     *
     * @internal
     */
    public function getConstructorParameters(string $class): ?array
    {
        return $this->getReflectionCache()->getConstructorParameters($class, function () use ($class) {
            $reflector   = $this->getReflectionClass($class);
            $constructor = $reflector->getConstructor();

            return $constructor ? $constructor->getParameters() : null;
        });
    }

    /**
     * Get the last parameter override.
     *
     * @return array<mixed>
     *
     * @internal
     */
    public function getLastParameterOverride(): array
    {
        return count($this->with) ? end($this->with) : [];
    }

    /**
     * Get all of the container's bindings.
     *
     * @return array<string, array{concrete: \Closure, shared: bool}>
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * Enable or disable the reflection cache.
     */
    public function clearCache(): self
    {
        $this->getReflectionCache()->clear();

        return $this;
    }

    /**
     * Remove all of the container's bindings and instances.
     */
    public function flush(): void
    {
        $this->bindings        = [];
        $this->instances       = [];
        $this->aliases         = [];
        $this->with            = [];
        $this->resolver        = null;
        $this->invoker         = null;
        $this->reflectionCache = null;
    }

    /**
     * Resolve the given type from the container.
     *
     * @param array<int|string, mixed> $parameters
     *
     * @throws BindingResolutionException
     */
    protected function resolve(string $abstract, array $parameters = [], bool $useCache = true): mixed
    {
        $abstract = $this->getAlias($abstract);

        if ($useCache && isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $this->with[] = $parameters;

        $concrete = $this->getConcrete($abstract);

        if ($concrete instanceof \Closure) {
            $object = $this->call($concrete, $this->getLastParameterOverride());
        } else {
            $object = $this->build($concrete, $this->getLastParameterOverride());
        }

        if ($useCache || $this->isShared($abstract)) {
            $this->instances[$abstract] = $object;
        }

        array_pop($this->with);

        return $object;
    }

    /**
     * Determine if a given type is shared.
     */
    protected function isShared(string $abstract): bool
    {
        return isset($this->bindings[$abstract]['shared'])
               && true === $this->bindings[$abstract]['shared'];
    }

    /**
     * Get the concrete type for a given abstract.
     */
    protected function getConcrete(string $abstract): mixed
    {
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]['concrete'];
        }

        return $abstract;
    }

    /**
     * Get the Closure to be used when building a type.
     *
     * @return \Closure(self, array<mixed>):mixed
     */
    protected function getClosure(string $abstract, string $concrete): \Closure
    {
        return function ($container, $parameters = []) use ($abstract, $concrete) {
            if ($abstract == $concrete) {
                return $container->build($concrete, $parameters);
            }

            return $container->resolve($concrete, $parameters, false);
        };
    }

    /**
     * Determine if the given type is a primitive type.
     */
    protected function isPrimitiveType(string $type): bool
    {
        static $types = ['int' => true, 'float' => true, 'string' => true, 'bool' => true, 'array' => true, 'object' => true, 'callable' => true, 'iterable' => true, 'resource' => true];

        return isset($types[$type]);
    }

    /**
     * @param array<string, true> $resolving
     */
    private function resolveAlias(string $abstract, array $resolving): string
    {
        if (false === isset($this->aliases[$abstract])) {
            return $abstract;
        }

        if (isset($resolving[$abstract])) {
            throw new CircularAliasException($abstract);
        }

        $resolving[$abstract] = true;

        return $this->resolveAlias($this->aliases[$abstract], $resolving);
    }

    private function getResolver(): Resolver
    {
        if (null === $this->resolver) {
            $this->resolver = new Resolver($this);
        }

        return $this->resolver;
    }

    private function getInvoker(): Invoker
    {
        if (null === $this->invoker) {
            $this->invoker = new Invoker($this);
        }

        return $this->invoker;
    }

    private function getReflectionCache(): ReflectionCache
    {
        if (null === $this->reflectionCache) {
            $this->reflectionCache = new ReflectionCache();
        }

        return $this->reflectionCache;
    }

    private function getInjector(): Injector
    {
        if (null === $this->injector) {
            $this->injector = new Injector($this);
        }

        return $this->injector;
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
        $this->bind($offset, $value instanceof \Closure ? $value : fn () => $value);
    }

    /**
     * Unset the value.
     *
     * @param string $offset
     */
    public function offsetUnset($offset): void
    {
        $offset = $this->getAlias($offset);
        unset($this->instances[$offset]);
        unset($this->bindings[$offset]);
        foreach ($this->aliases as $alias => $abstract) {
            if ($abstract === $offset || $alias === $offset) {
                unset($this->aliases[$alias]);
            }
        }
    }
}
