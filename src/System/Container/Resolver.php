<?php

declare(strict_types=1);

namespace System\Container;

use System\Container\Exceptions\BindingResolutionException;

/**
 * @internal
 */
final class Resolver
{
    private Container $container;

    /**
     * The stack of concretions currently being built.
     *
     * @var array<string, bool>
     */
    private array $buildStack = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Instantiate a concrete instance of the given type.
     *
     * @param array<int|string, mixed> $parameters
     *
     * @throws BindingResolutionException
     */
    public function resolveClass(string $concrete, array $parameters = []): mixed
    {
        $reflector = $this->container->getReflectionClass($concrete);

        if (false === $reflector->isInstantiable()) {
            throw new BindingResolutionException("Target [{$concrete}] is not instantiable.");
        }

        if (isset($this->buildStack[$concrete])) {
            $path = implode(' -> ', array_keys($this->buildStack)) . ' -> ' . $concrete;
            throw new BindingResolutionException("Circular dependency detected while trying to build [{$concrete}]. Path: {$path}.");
        }

        $this->buildStack[$concrete] = true;

        try {
            $dependencies = $this->container->getConstructorParameters($concrete);

            if (is_null($dependencies)) {
                return new $concrete();
            }

            $instances = $this->resolveDependencies($dependencies, $parameters);

            return $reflector->newInstanceArgs($instances);
        } finally {
            unset($this->buildStack[$concrete]);
        }
    }

    /**
     * Resolve all of the dependencies from the ReflectionParameters.
     *
     * @param \ReflectionParameter[]   $dependencies
     * @param array<int|string, mixed> $parameters
     *
     * @return array<mixed>
     */
    private function resolveDependencies(array $dependencies, array $parameters = []): array
    {
        $results = [];

        foreach ($dependencies as $dependency) {
            $name = $dependency->name;

            if (array_key_exists($name, $parameters)) {
                $results[] = $parameters[$name];
                continue;
            }

            if (array_key_exists($dependency->getPosition(), $parameters)) {
                $results[] = $parameters[$dependency->getPosition()];
                continue;
            }

            $override = $this->container->getLastParameterOverride();
            if (array_key_exists($name, $override)) {
                $results[] = $override[$name];
                continue;
            }

            $results[] = $this->resolveParameterDependency($dependency);
        }

        return $results;
    }

    /**
     * Resolve a single parameter dependency.
     *
     * @throws BindingResolutionException
     */
    public function resolveParameterDependency(\ReflectionParameter $parameter): mixed
    {
        $type = $parameter->getType();

        if (null === $type) {
            return $this->resolveUnTypedParameter($parameter);
        }

        if ($type instanceof \ReflectionIntersectionType) {
            throw new BindingResolutionException("Intersection types are not supported for dependency resolution of [{$parameter}] in class {$parameter->getDeclaringClass()->getName()}");
        }

        $isUnion    = $type instanceof \ReflectionUnionType;
        $types      = $isUnion ? $type->getTypes() : [$type];
        $classTypes = array_filter($types, fn ($t): bool => $t instanceof \ReflectionNamedType && false === $t->isBuiltin());

        foreach ($classTypes as $classType) {
            $name = $classType->getName();
            if ($this->container->bound($name)) {
                return $this->container->get($name);
            }
        }

        if (false === $isUnion && false === empty($classTypes)) {
            $firstClass = array_values($classTypes)[0];

            return $this->container->make($firstClass->getName());
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        if ($type->allowsNull()) {
            return null;
        }

        return $this->unresolvable($parameter, $isUnion);
    }

    /**
     * @throws BindingResolutionException
     */
    private function resolveUnTypedParameter(\ReflectionParameter $parameter): mixed
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        return $this->unresolvable($parameter);
    }

    /**
     * @phpstan-return never
     *
     * @throws BindingResolutionException
     */
    private function unresolvable(\ReflectionParameter $parameter, bool $isUnion = false): void
    {
        $class     = $parameter->getDeclaringClass();
        $className = $class ? $class->getName() : 'unknown';
        $message   = $isUnion
            ? 'none of the types in the union are bound in the container'
            : 'the dependency is not bound and cannot be autowired';

        throw new BindingResolutionException("Unresolvable dependency resolving [{$parameter}] in class {$className}: {$message}");
    }
}
