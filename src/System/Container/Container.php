<?php

declare(strict_types=1);

namespace System\Container;

use Closure;
use System\Container\Exceptions\BindingResolutionException;
use System\Container\Exceptions\EntryNotFoundException;

class Container implements \ArrayAccess
{
    /**
     * The container's bindings.
     */
    protected array $bindings = [];

    /**
     * The container's shared instances (singleton cache).
     */
    protected array $instances = [];

    /**
     * The registered type aliases.
     *
     * @var array<string, string>
     */
    protected array $aliases = [];

    /**
     * The stack of concretions currently being built.
     */
    protected array $buildStack = [];

    /**
     * The parameter override stack.
     */
    protected array $with = [];

    /**
     * The cached reflection data.
     */
    protected array $reflectionCache = [];

    /**
     * The cached constructor data.
     */
    protected array $constructorCache = [];

    /**
     * Whether the reflection cache is enabled.
     */
    protected bool $cacheEnabled = true;

    /**
     * Register a binding with the container.
     */
    public function bind(string $abstract, \Closure|string|null $concrete = null, bool $shared = false): void
    {
        $abstract = $this->getAlias($abstract);

        $concrete = $concrete ?? $abstract;

        // If the concrete is not a Closure, we will make it one.
        if (false === $concrete instanceof \Closure) {
            $concrete = $this->getClosure($abstract, $concrete);
        }

        $this->bindings[$abstract] = compact('concrete', 'shared');
    }

    /**
     * Get the Closure to be used when building a type.
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
     * Define an object or a value in the container.
     */
    public function set(string $name, mixed $value): void
    {
        $name = $this->getAlias($name);

        // If value is already an instance, store it directly
        if (is_object($value) && false === $value instanceof \Closure) {
            $this->instances[$name] = $value;

            return;
        }

        // Otherwise, bind it as shared (singleton)
        $this->bind($name, $value, true);
    }

    /**
     * Alias a type to a different name.
     */
    public function alias(string $abstract, string $alias): void
    {
        if ($abstract === $alias) {
            throw new \Exception("{$abstract} is aliased to itself.");
        }

        $this->aliases[$alias] = $abstract;
    }

    /**
     * Get the alias for an abstract if available.
     */
    public function getAlias(string $abstract): string
    {
        return isset($this->aliases[$abstract])
            ? $this->getAlias($this->aliases[$abstract])
            : $abstract;
    }

    /**
     * Resolve and return an entry from the container (singleton behavior - PHP-DI compatible).
     *
     * @template T
     *
     * @return mixed|T
     *
     * @throws Exception\EntryNotFoundException
     * @throws Exception\BindingResolutionException
     */
    public function get(string $name): mixed
    {
        if (false === $this->has($name)) {
            // Check if it's a valid class before throwing EntryNotFoundException
            if (false === class_exists($name) && false === interface_exists($name)) {
                throw new EntryNotFoundException("No entry was found for '{$name}' identifier.");
            }
        }

        return $this->resolve($name, [], true);
    }

    /**
     * Resolve a new instance from the container (always fresh - PHP-DI compatible).
     *
     * @template T
     *
     * @param string|class-string<T> $name
     *
     * @return mixed|T
     *
     * @throws Exception\BindingResolutionException
     */
    public function make(string $name, array $parameters = []): mixed
    {
        return $this->resolve($name, $parameters, false);
    }

    /**
     * Resolve the given type from the container.
     *
     * @throws Exception\BindingResolutionException
     */
    protected function resolve(string $abstract, array $parameters = [], bool $useCache = true): mixed
    {
        $abstract = $this->getAlias($abstract);

        // If using cache and instance exists, return it (singleton behavior)
        if ($useCache && isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $this->with[] = $parameters;

        $concrete = $this->getConcrete($abstract);

        // If we have a Closure as concrete, execute it
        if ($concrete instanceof \Closure) {
            $object = $concrete($this, $this->getLastParameterOverride());
        } else {
            $object = $this->build($concrete, $this->getLastParameterOverride());
        }

        // Store instance if using cache or if binding is marked as shared
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
               && $this->bindings[$abstract]['shared'] === true;
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
     * @throws Exception\BindingResolutionException
     */
    public function build(string $concrete, array $parameters = []): mixed
    {
        // If the concrete is actually a Closure, just execute it and return the result.
        if ($concrete instanceof \Closure) {
            return $concrete($this, $parameters);
        }

        $reflector = $this->getReflectionClass($concrete);

        // If the type is not instantiable, we'll throw an exception.
        if (false === $reflector->isInstantiable()) {
            throw new BindingResolutionException("Target [$concrete] is not instantiable.");
        }

        $this->buildStack[] = $concrete;

        $dependencies = $this->getConstructorParameters($concrete);

        // If there are no constructors, that means there are no dependencies
        if (is_null($dependencies)) {
            array_pop($this->buildStack);

            return new $concrete();
        }

        // Merge provided parameters with constructor dependencies
        $instances = $this->resolveDependencies($dependencies, $parameters);

        array_pop($this->buildStack);

        return $reflector->newInstanceArgs($instances);
    }

    /**
     * Get a reflection class instance for the given class.
     *
     * @throws \ReflectionException
     */
    protected function getReflectionClass(string $class): \ReflectionClass
    {
        if ($this->cacheEnabled && isset($this->reflectionCache[$class])) {
            return $this->reflectionCache[$class];
        }

        // Check if class exists first to avoid reflection exception
        if (false === class_exists($class) && false === interface_exists($class)) {
            throw new \ReflectionException("Class {$class} does not exist");
        }

        $reflector = new \ReflectionClass($class);

        if ($this->cacheEnabled) {
            $this->reflectionCache[$class] = $reflector;
        }

        return $reflector;
    }

    /**
     * Get constructor parameters for a class (with caching).
     */
    protected function getConstructorParameters(string $class): ?array
    {
        if ($this->cacheEnabled && isset($this->constructorCache[$class])) {
            return $this->constructorCache[$class];
        }

        $reflector   = $this->getReflectionClass($class);
        $constructor = $reflector->getConstructor();

        $parameters = $constructor ? $constructor->getParameters() : null;

        if ($this->cacheEnabled) {
            $this->constructorCache[$class] = $parameters;
        }

        return $parameters;
    }

    /**
     * Resolve all of the dependencies from the ReflectionParameters.
     *
     * @return array
     */
    protected function resolveDependencies(array $dependencies, array $parameters = [])
    {
        $results = [];

        foreach ($dependencies as $dependency) {
            $name = $dependency->name;

            // Check if parameter is provided by name
            if (array_key_exists($name, $parameters)) {
                $results[] = $parameters[$name];
                continue;
            }

            // Check if parameter is provided by position
            if (array_key_exists($dependency->getPosition(), $parameters)) {
                $results[] = $parameters[$dependency->getPosition()];
                continue;
            }

            // Check override stack
            $override = $this->getLastParameterOverride();
            if (array_key_exists($name, $override)) {
                $results[] = $override[$name];
                continue;
            }

            // Try to resolve the dependency from the container
            $results[] = $this->resolveParameterDependency($dependency);
        }

        return $results;
    }

    /**
     * Resolve a single parameter dependency.
     *
     * @return mixed
     *
     * @throws Exception\BindingResolutionException
     */
    protected function resolveParameterDependency(\ReflectionParameter $parameter)
    {
        $type = $parameter->getType();

        // If the parameter has no type, we'll check if it has a default value.
        if (false === $type) {
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            throw new BindingResolutionException("Unresolvable dependency resolving [$parameter] in class {$parameter->getDeclaringClass()->getName()}");
        }

        $typeName = $type->getName();

        // If the type is a primitive, we can't resolve it automatically.
        if ($this->isPrimitiveType($typeName)) {
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            throw new BindingResolutionException("Unresolvable dependency resolving [$parameter] in class {$parameter->getDeclaringClass()->getName()}: primitive type with no default value");
        }

        try {
            return $this->get($typeName);
        } catch (EntryNotFoundException $e) {
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            throw new BindingResolutionException("Unresolvable dependency resolving [$parameter] in class {$parameter->getDeclaringClass()->getName()}: {$e->getMessage()}");
        }
    }

    /**
     * Determine if the given type is a primitive type.
     */
    protected function isPrimitiveType(string $type): bool
    {
        return in_array($type, ['int', 'float', 'string', 'bool', 'array', 'object', 'callable', 'iterable', 'resource']);
    }

    /**
     * Get the last parameter override.
     */
    protected function getLastParameterOverride(): array
    {
        return count($this->with) ? end($this->with) : [];
    }

    /**
     * Call the given callable and inject its dependencies.
     */
    public function call(callable|array|string $callable, array $parameters = []): mixed
    {
        if (false === is_callable($callable)) {
            throw new \InvalidArgumentException('Callback is not callable.');
        }

        // Handle array callable [object, method] or [class, method]
        if (is_array($callable)) {
            return $this->callMethod($callable[0], $callable[1], $parameters);
        }

        // Handle closure or function
        $reflector    = new \ReflectionFunction($callable);
        $dependencies = $this->resolveFunctionDependencies($reflector, $parameters);

        return call_user_func_array($callable, $dependencies);
    }

    /**
     * Call a method and inject its dependencies.
     */
    protected function callMethod(object|string $instance, string $method, array $parameters = []): mixed
    {
        // If instance is a class name, resolve it first
        if (is_string($instance)) {
            $instance = $this->get($instance);
        }

        $reflector    = new \ReflectionMethod($instance, $method);
        $dependencies = $this->resolveFunctionDependencies($reflector, $parameters);

        return $reflector->invokeArgs($instance, $dependencies);
    }

    /**
     * Resolve function dependencies.
     */
    protected function resolveFunctionDependencies(\ReflectionFunctionAbstract $reflection, array $parameters = []): array
    {
        $dependencies = [];

        foreach ($reflection->getParameters() as $parameter) {
            $name = $parameter->getName();

            // Check if provided by name
            if (array_key_exists($name, $parameters)) {
                $dependencies[] = $parameters[$name];
                unset($parameters[$name]);
                continue;
            }

            // Check if provided by position
            if (array_key_exists($parameter->getPosition(), $parameters)) {
                $dependencies[] = $parameters[$parameter->getPosition()];
                continue;
            }

            // Try to resolve from container if type-hinted
            if ($parameter->getType() && false === $parameter->getType()->isBuiltin()) {
                $dependencies[] = $this->get($parameter->getType()->getName());
                continue;
            }

            // Use default value if available
            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
                continue;
            }

            // Use remaining indexed parameters
            if (count($parameters)) {
                $dependencies[] = array_shift($parameters);
                continue;
            }

            throw new BindingResolutionException("Unable to resolve dependency [{$parameter}] in callable");
        }

        return array_merge($dependencies, array_values($parameters));
    }

    /**
     * Inject dependencies on an existing instance.
     *
     * @template T
     *
     * @param object|T $instance Object to perform injection upon
     *
     * @return object|T $instance Returns the same instance
     */
    public function injectOn(object $instance): object
    {
        // Get all public methods
        $class     = get_class($instance);
        $reflector = $this->getReflectionClass($class);

        // Look for methods with @Inject annotation or specific naming pattern
        foreach ($reflector->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            // Skip constructor and static methods
            if ($method->isConstructor() || $method->isStatic()) {
                continue;
            }

            // Check if method name starts with 'set' (setter injection pattern)
            if (strpos($method->getName(), 'set') === 0 && $method->getNumberOfParameters() > 0) {
                $parameters = $method->getParameters();

                // Only inject if all parameters are type-hinted with classes
                $canInject = true;
                foreach ($parameters as $param) {
                    $type = $param->getType();
                    if (false === $type || $type->isBuiltin()) {
                        $canInject = false;
                        break;
                    }
                }

                if ($canInject) {
                    try {
                        $dependencies = $this->resolveDependencies($parameters);
                        $method->invokeArgs($instance, $dependencies);
                    } catch (BindingResolutionException $e) {
                        // Skip if can't resolve
                        continue;
                    }
                }
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
     * Determine if a given identifier has a value or binding (PSR-11).
     */
    public function has(string $id): bool
    {
        return $this->bound($id) || class_exists($id) || interface_exists($id);
    }

    /**
     * Enable or disable the reflection cache.
     */
    public function enableCache(bool $enabled = true): self
    {
        $this->cacheEnabled = $enabled;

        return $this;
    }

    /**
     * Clear the reflection cache.
     */
    public function clearCache(): self
    {
        $this->reflectionCache  = [];
        $this->constructorCache = [];

        return $this;
    }

    /**
     * Get all of the container's bindings.
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
        $this->bindings         = [];
        $this->instances        = [];
        $this->aliases          = [];
        $this->buildStack       = [];
        $this->with             = [];
        $this->reflectionCache  = [];
        $this->constructorCache = [];
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
