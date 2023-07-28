<?php

declare(strict_types=1);

namespace System\Router;

use System\Text\Str;

class ResourceControllerCollection
{
    private string $class_name;

    public function __construct(string $class_name)
    {
        $this->class_name = $class_name;
    }

    /**
     * Expect resource with route name.
     *
     * @param string[] $resources
     */
    public function only($resources): void
    {
        $map = array_filter(
            ResourceController::method(),
            fn ($resource) => !in_array($resource, $resources, true)
        );
        foreach ($map as $resource) {
            $name = "{$this->class_name}.{$resource}";
            if (Router::has($name)) {
                Router::removeRoutes($name);
            }
        }
    }

    /**
     * Expect resource with route name.
     *
     * @param string[] $resources
     */
    public function except($resources): void
    {
        foreach ($resources as $resource) {
            $name = "{$this->class_name}.{$resource}";
            if (Router::has($name)) {
                Router::removeRoutes($name);
            }
        }
    }

    /**
     * Map resource with exits route resource.
     *
     * @param string[] $resources
     */
    public function map($resources): void
    {
        $diff = array_diff_key(ResourceController::method(), $resources);
        $this->except($diff);

        foreach (Router::getRoutes() as $route) {
            foreach ($resources as $key => $resource) {
                $name = "{$this->class_name}.{$key}";
                if ($name === $route['name']) {
                    $route['function'][1] = $resource;
                    Router::changeRoutes($name, $route);
                    break;
                }
            }
        }
    }

    public function missing(callable $callable): self
    {
        foreach (Router::getRoutes() as $route) {
            $name = $route['name'];
            if (Str::startsWith($name, "{$this->class_name}.")) {
                [$class, $method] = $route['function'];
                if (!method_exists($class, $method)) {
                    $route['function'] = $callable;
                    Router::changeRoutes($name, $route);
                }
            }
        }

        return $this;
    }
}
