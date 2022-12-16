<?php

declare(strict_types=1);

namespace System\Router;

use System\Collection\Collection;

class ResourceController
{
    /** @var Collection */
    private $resource;

    /**
     * @param class-string $class_name
     */
    public function __construct(string $url, $class_name)
    {
        $this->resource = new Collection([]);
        $this->ganerate($url, $class_name);
    }

    /**
     * @param class-string $class_name
     */
    public function ganerate(string $uri, $class_name): self
    {
        $uri = Router::$group['prefix'] . $uri;

        // get mapper
        $map = [
          'index' => 'index',
          'store' => 'store',
        ];

        if (property_exists($class_name, 'resource_map')) {
            $reflection   = new \ReflectionClass($class_name);
            $resource_map = $reflection->getDefaultProperties()['resource_map'];
            $map          = array_merge($map, $resource_map);
        }

        // index
        if (method_exists($class_name, $map['index'])) {
            $this->resource->set($map['index'], new Route([
                'expression' => Router::mapPatterns($uri),
                'function'   => [$class_name, $map['index']],
                'method'     => 'get',
                'middleware' => Router::$group['middleware'] ?? [],
            ]));
        }

        // store
        if (method_exists($class_name, $map['store'])) {
            $this->resource->set($map['store'], new Route([
                'expression' => Router::mapPatterns($uri),
                'function'   => [$class_name, $map['store']],
                'method'     => 'post',
                'middleware' => Router::$group['middleware'] ?? [],
            ]));
        }

        return $this;
    }

    public function get(): Collection
    {
        return $this->resource;
    }

    public function only(array $resource): self
    {
        $this->resource->only($resource);

        return $this;
    }

    public function except(array $resource): self
    {
        $this->resource->except($resource);

        return $this;
    }
}
