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
    protected ?Resolver $resolver = null;

    /**
     * The callable invoker instance.
     */
    protected ?Invoker $invoker = null;

    /**
     * The reflection cache instance.
     */
    protected ?ReflectionCache $reflectionCache = null;

    /**
     * The parameter override stack.
     *
     * @var list<array<mixed>>
     */
    protected array $with = [];

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
     * Resolve and return an entry from the container (singleton behavior - PHP-DI compatible).
     *
     * @throws EntryNotFoundException
     * @throws BindingResolutionException
     */
    public function get(string $name): mixed
    {
        if (false === $this->has($name)) {
            if (false === class_exists($name) && false === interface_exists($name)) {
                throw new EntryNotFoundException($name);
            }
        }

        return $this->resolve($name, [], true);
    }

    /**
     * Resolve a new instance from the container (always fresh - PHP-DI compatible).
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
     * Determine if a given identifier has a value or binding.
     */
    public function has(string $id): bool
    {
        return $this->bound($id) || class_exists($id) || interface_exists($id);
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
     * Lazy load the resolver instance.
     */
    private function getResolver(): Resolver
    {
        if (null === $this->resolver) {
            $this->resolver = new Resolver($this);
        }

        return $this->resolver;
    }

    /**
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

    private function getReflectionCache(): ReflectionCache
    {
        if (null === $this->reflectionCache) {
            $this->reflectionCache = new ReflectionCache();
        }

        return $this->reflectionCache;
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

    private function getInvoker(): Invoker
    {
        if (null === $this->invoker) {
            $this->invoker = new Invoker($this);
        }

        return $this->invoker;
    }

    /**
     * Inject dependencies on an existing instance.
     *
     * @param object $instance Object to perform injection upon
     */
    public function injectOn(object $instance): object
    {
        $class     = $instance::class;
        $reflector = $this->getReflectionClass($class);

        // Look for method with #[Inject] attribute
        foreach ($reflector->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isConstructor() || $method->isStatic()) {
                continue;
            }

            $injects    = [];
            $attributes = $method->getAttributes(Inject::class);
            if (0 === count($attributes)) {
                continue;
            }

            $method_injects = $attributes[0]->newInstance()->getName();
            if (is_array($method_injects)) {
                $injects = $method_injects;
            }

            if ($method->getNumberOfParameters() > 0) {
                $parameters = $method->getParameters();

                // Only inject if all parameters are type-hinted with classes
                $canInject = true;
                foreach ($parameters as $param) {
                    // Check for #[Inject] on the parameter itself
                    $paramAttributes = $param->getAttributes(Inject::class);
                    if (false === empty($paramAttributes)) {
                        continue;
                    }

                    if (isset($injects[$param->name])) {
                        // An explicit binding is provided via the Inject attribute
                        continue;
                    }

                    $type = $param->getType();
                    if (!$type || ($type instanceof \ReflectionNamedType && $type->isBuiltin())) {
                        $canInject = false;
                        break;
                    }
                }

                if ($canInject) {
                    try {
                        $dependencies = [];
                        foreach ($parameters as $param) {
                            $paramName = $param->getName();

                            $paramAttributes = $param->getAttributes(Inject::class);
                            if (false === empty($paramAttributes)) {
                                $paramInject    = $paramAttributes[0]->newInstance();
                                $abstract       = $paramInject->getName();
                                $dependencies[] = $this->get($abstract);
                                continue;
                            }

                            if (array_key_exists($paramName, $injects)) {
                                $dependencies[] = $injects[$paramName];
                                continue;
                            }

                            // Only resolve if not provided in the inject attribute.
                            $dependencies[] = $this->getResolver()->resolveParameterDependency($param);
                        }

                        $method->invokeArgs($instance, $dependencies);
                    } catch (BindingResolutionException $e) {
                        // Suppress exception if injection fails,
                        // allowing other injections to proceed.
                        continue;
                    }
                }
            }
        }

        // Look for property with #[Inject] attribute
        foreach ($reflector->getProperties(\ReflectionMethod::IS_PUBLIC) as $method) {
            $attributes = $method->getAttributes(Inject::class);
            if (0 === count($attributes)) {
                continue;
            }

            $property_inject = $attributes[0]->newInstance();
            $abstract        = $property_inject->getName();

            try {
                if (is_array($abstract)) {
                    continue;
                }

                $dependency = $this->get($abstract);
                $method->setValue($instance, $dependency);
            } catch (BindingResolutionException $e) {
                // Suppress exception if injection fails,
                // allowing other injections to proceed.
                continue;
            }
        }

        return $instance;
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
     * Enable or disable the reflection cache.
     */
    public function clearCache(): self
    {
        $this->getReflectionCache()->clear();

        return $this;
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
     * Remove all of the container's bindings and instances.
     */
    public function flush(): void
    {
        $this->bindings  = [];
        $this->instances = [];
        $this->aliases   = [];
        $this->with      = [];

        $this->resolver        = null;
        $this->invoker         = null;
        $this->reflectionCache = null;
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
        return $this->get($offset);
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
        $offset = $this->getAlias($offset); // Ensure we're unsetting the canonical name

        unset($this->instances[$offset]);
        unset($this->bindings[$offset]);
        // Also remove from aliases if it was an alias itself, or an alias pointing to it
        foreach ($this->aliases as $alias => $abstract) {
            if ($abstract === $offset || $alias === $offset) {
                unset($this->aliases[$alias]);
            }
        }
        unset($this->reflectionCache[$offset]);
        unset($this->constructorCache[$offset]);
    }
}
