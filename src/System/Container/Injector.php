<?php

declare(strict_types=1);

namespace System\Container;

use System\Container\Attribute\Inject;
use System\Container\Exceptions\BindingResolutionException;

/**
 * @internal
 */
final class Injector
{
    private ?Resolver $resolver;

    public function __construct(private Container $container)
    {
        $this->resolver = new Resolver($container);
    }

    /**
     * Injects dependencies on an existing instance.
     *
     * @param object $instance Object to perform injection upon
     */
    public function inject(object $instance): object
    {
        $reflector = $this->container->getReflectionClass($instance::class);

        $this->injectMethods($instance, $reflector);
        $this->injectProperties($instance, $reflector);

        return $instance;
    }

    /**
     * Injects dependencies into public methods with #[Inject] attributes.
     *
     * @param \ReflectionClass<object> $reflector
     */
    private function injectMethods(object $instance, \ReflectionClass $reflector): void
    {
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
                    $hasParamInject  = false === empty($paramAttributes);
                    $hasMethodInject = isset($injects[$param->name]);

                    if ($hasParamInject || $hasMethodInject) {
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
                                $dependencies[] = $this->container->get($abstract);
                                continue;
                            }

                            if (array_key_exists($paramName, $injects)) {
                                $dependencies[] = $injects[$paramName];
                                continue;
                            }

                            // Only resolve if not provided in the inject attribute.
                            $dependencies[] = $this->resolver->resolveParameterDependency($param);
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
    }

    /**
     * Injects dependencies into public properties with #[Inject] attributes.
     *
     * @param \ReflectionClass<object> $reflector
     */
    private function injectProperties(object $instance, \ReflectionClass $reflector): void
    {
        // Look for property with #[Inject] attribute
        foreach ($reflector->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $attributes = $property->getAttributes(Inject::class);
            if (0 === count($attributes)) {
                continue;
            }

            $property_inject = $attributes[0]->newInstance();
            $abstract        = $property_inject->getName();

            try {
                if (is_array($abstract)) { // This check should ideally be for string or mixed
                    continue;
                }

                $dependency = $this->container->get($abstract);
                $property->setValue($instance, $dependency);
            } catch (BindingResolutionException $e) {
                // Suppress exception if injection fails,
                // allowing other injections to proceed.
                continue;
            }
        }
    }
}
