<?php

declare(strict_types=1);

namespace System\Router;

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
            ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'],
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
}
