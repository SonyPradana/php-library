<?php

declare(strict_types=1);

namespace System\Container;

use System\Container\Exceptions\BindingResolutionException;

/**
 * @internal
 */
final class Invoker
{
    public function __construct(
        private Container $container,
    ) {
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
            return $this->callMethod(instance: $callable[0], method: $callable[1], parameters: $parameters);
        }

        // Handle string ClassName::class (invokable)
        if (is_string($callable) && class_exists($callable)) {
            $reflectionClass = new \ReflectionClass($callable);
            if (false === $reflectionClass->hasMethod('__invoke')) {
                throw new BindingResolutionException("Class {$callable} does not have an __invoke() method. Cannot be used as invokable.");
            }

            $instance     = $this->container->get($callable);
            $invokeMethod = $this->container->getReflectionMethod($callable, '__invoke');
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
            $reflectionMethod = $this->container->getReflectionMethod($callable, '__invoke');
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
    private function callMethod(object|string $instance, string $method, array $parameters = []): mixed
    {
        // resolve class name
        if (is_string($instance)) {
            $instance = $this->container->get($instance);
        }

        $reflector    = $this->container->getReflectionMethod($instance, $method);
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
    private function resolveFunctionDependencies(\ReflectionFunctionAbstract $reflection, array $parameters = []): array
    {
        $dependencies = [];

        foreach ($reflection->getParameters() as $parameter) {
            $name = $parameter->getName();

            if (array_key_exists($name, $parameters)) {
                $dependencies[] = $parameters[$name];
                unset($parameters[$name]);
                continue;
            }

            if (array_key_exists($parameter->getPosition(), $parameters)) {
                $dependencies[] = $parameters[$parameter->getPosition()];
                continue;
            }

            if ($parameter->getType() instanceof \ReflectionNamedType && false === $parameter->getType()->isBuiltin()) {
                $dependencies[] = $this->container->get($parameter->getType()->getName());
                continue;
            }

            if ($parameter->getName() === 'container' && $parameter->getType() === null) {
                $dependencies[] = $this->container;
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
                continue;
            }

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
                if ($type instanceof \ReflectionNamedType) {
                    // If it's a non-built-in class type,
                    // resolve it from the container
                    if (false === $type->isBuiltin()) {
                        $dependencies[] = $this->container->get($type->getName());
                        continue;
                    }
                }
            }

            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();

                continue;
            }

            throw new BindingResolutionException("Cannot resolve parameter \${$name} in " . $instance::class . '::__invoke()');
        }

        return $dependencies;
    }
}
