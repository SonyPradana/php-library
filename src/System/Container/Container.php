<?php

declare(strict_types=1);

namespace System\Container;

use Closure;
use System\Container\Exceptions\BindingResolutionException;
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
     * The stack of concretions currently being built.
     *
     * @var array<string, bool>
     */
    protected array $buildStack = [];

    /**
     * The parameter override stack.
     *
     * @var list<array<mixed>>
     */
    protected array $with = [];

    /**
     * The cached reflection data.
     *
     * @var array<string, \ReflectionClass<object>>
     */
    protected array $reflectionCache = [];

    /**
     * The cached constructor data.
     *
     * @var array<string, array<\ReflectionParameter>|null>
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
        // If the value is a Closure, it's a factory for a shared instance.
        if ($value instanceof \Closure) {
            $this->bind($name, $value, true);

            return;
        }

        $name = $this->getAlias($name);

        // Otherwise, store the value directly as a resolved, shared instance.
        $this->instances[$name] = $value;
        // And ensure that any 'make' calls also return this specific instance.
        $this->bindings[$name] = [
            'concrete' => fn () => $this->instances[$name],
            'shared'   => true,
        ];
    }

    /**
     * Determine if a given identifier has a value or binding (PSR-11).
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
            throw new \Exception("{$abstract} is aliased to itself.");
        }

        $this->aliases[$alias] = $abstract;
    }

    /**
     * Get the alias for an abstract if available.
     */
    public function getAlias(string $abstract): string
    {
        $resolving = [];
        while (isset($this->aliases[$abstract])) {
            if (isset($resolving[$abstract])) {
                throw new \Exception("Circular alias reference detected for {$abstract}");
            }
            $resolving[$abstract] = true;
            $abstract             = $this->aliases[$abstract];
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
     * Resolve the given type from the container.
     *
     * @param array<int|string, mixed> $parameters
     *
     * @throws BindingResolutionException
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
            $object = $this->call($concrete, $this->getLastParameterOverride());
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
     * @param array<int|string, mixed> $parameters
     *
     * @throws BindingResolutionException
     */
    public function build(string|\Closure $concrete, array $parameters = []): mixed
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

        // Detect circular dependencies
        if (isset($this->buildStack[$concrete])) {
            throw new BindingResolutionException("Circular dependency detected while trying to build [{$concrete}]. Stack: [" . implode(' -> ', array_merge($this->buildStack, [$concrete])) . '].');
        }

        $this->buildStack[$concrete] = true;

        $dependencies = $this->getConstructorParameters($concrete);

        // If there are no constructors, that means there are no dependencies
        if (is_null($dependencies)) {
            unset($this->buildStack[$concrete]);

            return new $concrete();
        }

        // Merge provided parameters with constructor dependencies
        $instances = $this->resolveDependencies($dependencies, $parameters);

        unset($this->buildStack[$concrete]);

        return $reflector->newInstanceArgs($instances);
    }

    /**
     * Get a reflection class instance for the given class.
     *
     * @return \ReflectionClass<object>
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
     *
     * @return array<\ReflectionParameter>|null
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
     * @param \ReflectionParameter[]   $dependencies
     * @param array<int|string, mixed> $parameters
     *
     * @return array<mixed>
     */
    protected function resolveDependencies(array $dependencies, array $parameters = []): array
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
     * @throws BindingResolutionException
     */
    protected function resolveParameterDependency(\ReflectionParameter $parameter): mixed
    {
        $type = $parameter->getType();

        // If the parameter has no type,
        // we'll check if it has a default value.
        if (null === $type) {
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            throw new BindingResolutionException("Unresolvable dependency resolving [$parameter] in class {$parameter->getDeclaringClass()->getName()}");
        }

        if ($type instanceof \ReflectionIntersectionType) {
            throw new BindingResolutionException("Intersection types are not supported for dependency resolution of [$parameter] in class {$parameter->getDeclaringClass()->getName()}");
        }

        $isUnion    = $type instanceof \ReflectionUnionType;
        $types      = $isUnion ? $type->getTypes() : [$type];
        $classTypes = array_filter($types, fn ($t) => $t instanceof \ReflectionNamedType && false === $t->isBuiltin());

        // First, iterate and check for explicitly bound types.
        // This is safe for both union and single types.
        foreach ($classTypes as $classType) {
            $name = $classType->getName();
            if ($this->bound($name)) {
                return $this->get($name);
            }
        }

        if (false === $isUnion && false === empty($classTypes)) {
            try {
                $firstClass = array_values($classTypes)[0];

                return $this->get($firstClass->getName());
            } catch (BindingResolutionException $e) {
                // It failed to autowire.
                //  We'll fall through to the default/nullable check.
            }
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        if ($type->allowsNull()) {
            return null;
        }

        $class     = $parameter->getDeclaringClass();
        $className = $class ? $class->getName() : 'unknown';
        $message   = $isUnion
            ? 'none of the types in the union are bound in the container'
            : 'the dependency is not bound and cannot be autowired';

        throw new BindingResolutionException("Unresolvable dependency resolving [$parameter] in class {$className}: {$message}");
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
     *
     * @return array<mixed>
     */
    protected function getLastParameterOverride(): array
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
        // Handle array callable [object, method] or [class, method]
        if (is_array($callable)) {
            return $this->callMethod($callable[0], $callable[1], $parameters);
        }

        // Handle string ClassName::class (invokable)
        if (is_string($callable) && class_exists($callable)) {
            $reflectionClass = new \ReflectionClass($callable);
            if (false === $reflectionClass->hasMethod('__invoke')) {
                throw new BindingResolutionException("Class {$callable} does not have an __invoke() method. Cannot be used as invokable.");
            }

            $instance     = $this->get($callable);
            $invokeMethod = $reflectionClass->getMethod('__invoke');
            $dependencies = $this->resolveMethodDependencies($invokeMethod, $instance, $parameters);

            return $invokeMethod->invokeArgs($instance, $dependencies);
        }

        // Handle closure / function
        if (is_callable($callable) && !is_string($callable)) {
            $reflector    = new \ReflectionFunction($callable);
            $dependencies = $this->resolveFunctionDependencies($reflector, $parameters);

            return call_user_func_array($callable, $dependencies);
        }

        // Handle object (invokable object)
        if (is_object($callable) && method_exists($callable, '__invoke')) {
            $reflectionMethod = new \ReflectionMethod($callable, '__invoke');
            $dependencies     = $this->resolveMethodDependencies($reflectionMethod, $callable, $parameters);

            return $reflectionMethod->invokeArgs($callable, $dependencies);
        }

        throw new BindingResolutionException('Unable to call the given callable. Unsupported type.');
    }

    /**
     * Call a method and inject its dependencies.
     *
     * @param array<int|string, mixed> $parameters
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
     *
     * @param array<int|string, mixed> $parameters
     *
     * @return array<mixed>
     *
     * @throws BindingResolutionException
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
            if ($parameter->getType() instanceof \ReflectionNamedType && false === $parameter->getType()->isBuiltin()) {
                $dependencies[] = $this->get($parameter->getType()->getName());
                continue;
            }

            // This is a special case for the container itself.
            if ($parameter->getName() === 'container' && $parameter->getType() === null) {
                $dependencies[] = $this;
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
     * Resolve function dependencies.
     *
     * @param array<int|string, mixed> $parameters
     *
     * @return array<mixed>
     *
     * @throws BindingResolutionException
     */
    private function resolveMethodDependencies(\ReflectionMethod $method, object $instance, array $parameters = []): array
    {
        $dependencies = [];

        foreach ($method->getParameters() as $parameter) {
            $name = $parameter->getName();

            if (array_key_exists($name, $parameters)) {
                $dependencies[] = $parameters[$name];

                continue;
            }

            if ($type = $parameter->getType()) {
                if ($type instanceof \ReflectionNamedType && $type->isBuiltin()) {
                    $dependencies[] = $this->get($type->getName());

                    continue;
                }
            }

            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();

                continue;
            }

            throw new BindingResolutionException("Cannot resolve parameter \${$name} in " . get_class($instance) . '::__invoke()');
        }

        return $dependencies;
    }

    /**
     * Inject dependencies on an existing instance.
     *
     * @param object $instance Object to perform injection upon
     */
    public function injectOn(object $instance): object
    {
        // Get all public methods
        $class     = $instance::class;
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
                    if (!$type || ($type instanceof \ReflectionNamedType && $type->isBuiltin())) {
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
