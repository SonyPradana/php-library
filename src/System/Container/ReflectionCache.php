<?php

declare(strict_types=1);

namespace System\Container;

/**
 * @internal
 *
 * Manages caching for Reflection objects
 */
final class ReflectionCache
{
    /**
     * The cached reflection class data.
     *
     * @var array<string, \ReflectionClass<object>>
     */
    private array $classCache = [];

    /**
     * The cached reflection method data.
     *
     * @var array<string, array<string, \ReflectionMethod>>
     */
    private array $methodCache = [];

    /**
     * The cached constructor data.
     *
     * @var array<string, array<\ReflectionParameter>|null>
     */
    private array $constructorCache = [];

    public function getReflectionClass(string $class, \Closure $creator): \ReflectionClass
    {
        if (isset($this->classCache[$class])) {
            return $this->classCache[$class];
        }

        return $this->classCache[$class] = $creator();
    }

    public function getReflectionMethod(string $className, string $method, \Closure $creator): \ReflectionMethod
    {
        if (isset($this->methodCache[$className][$method])) {
            return $this->methodCache[$className][$method];
        }

        return $this->methodCache[$className][$method] = $creator();
    }

    /**
     * @param \Closure():(?array<\ReflectionParameter>) $creator
     *
     * @return ?array<\ReflectionParameter>
     */
    public function getConstructorParameters(string $class, \Closure $creator): ?array
    {
        if (array_key_exists($class, $this->constructorCache)) {
            return $this->constructorCache[$class];
        }

        return $this->constructorCache[$class] = $creator();
    }

    public function clear(): void
    {
        $this->classCache       = [];
        $this->methodCache      = [];
        $this->constructorCache = [];
    }
}
