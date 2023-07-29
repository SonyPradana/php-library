<?php

declare(strict_types=1);

namespace System\Router;

use System\Collection\Collection;
use System\Collection\CollectionImmutable;

class ResourceController
{
    /** @var Collection<string, Route> */
    private $resource;

    /**
     * List resource method.
     *
     * @return array<string, string>
     */
    public static function method()
    {
        return [
            'index'   => 'index',
            'create'  => 'create',
            'store'   => 'store',
            'show'    => 'show',
            'edit'    => 'edit',
            'update'  => 'update',
            'destroy' => 'destroy',
        ];
    }

    /**
     * @param class-string          $class_name
     * @param array<string, string> $map
     */
    public function __construct(string $url, $class_name, $map)
    {
        $this->resource = new Collection([]);
        $this->ganerate($url, $class_name, $map);
    }

    /**
     * @param class-string          $class_name
     * @param array<string, string> $map
     */
    public function ganerate(string $uri, $class_name, $map): self
    {
        $uri  = Router::$group['prefix'] . $uri;

        if (array_key_exists('index', $map)) {
            $this->resource->set($map['index'],
                (new Route([
                    'expression' => Router::mapPatterns($uri),
                    'function'   => [$class_name, $map['index']],
                    'method'     => 'get',
                    'middleware' => Router::$group['middleware'] ?? [],
                ]))->name("{$class_name}.index")
            );
        }

        if (array_key_exists('create', $map)) {
            $this->resource->set($map['create'],
                (new Route([
                    'expression' => Router::mapPatterns("{$uri}create"),
                    'function'   => [$class_name, $map['create']],
                    'method'     => 'get',
                    'middleware' => Router::$group['middleware'] ?? [],
                ]))->name("{$class_name}.create")
            );
        }

        if (array_key_exists('store', $map)) {
            $this->resource->set($map['store'],
                (new Route([
                    'expression' => Router::mapPatterns($uri),
                    'function'   => [$class_name, $map['store']],
                    'method'     => 'post',
                    'middleware' => Router::$group['middleware'] ?? [],
                ]))->name("{$class_name}.store")
            );
        }

        if (array_key_exists('show', $map)) {
            $this->resource->set($map['show'],
                (new Route([
                    'expression' => Router::mapPatterns("{$uri}(:id)"),
                    'function'   => [$class_name, $map['show']],
                    'method'     => 'get',
                    'middleware' => Router::$group['middleware'] ?? [],
                ]))->name("{$class_name}.show")
            );
        }

        if (array_key_exists('edit', $map)) {
            $this->resource->set($map['edit'],
                (new Route([
                    'expression' => Router::mapPatterns("{$uri}(:id)/edit"),
                    'function'   => [$class_name, $map['edit']],
                    'method'     => 'get',
                    'middleware' => Router::$group['middleware'] ?? [],
                ]))->name("{$class_name}.edit")
            );
        }

        if (array_key_exists('update', $map)) {
            $this->resource->set($map['update'],
                (new Route([
                    'expression' => Router::mapPatterns("{$uri}(:id)"),
                    'function'   => [$class_name, $map['update']],
                    'method'     => ['put', 'patch'],
                    'middleware' => Router::$group['middleware'] ?? [],
                ]))->name("{$class_name}.update")
            );
        }

        if (array_key_exists('destroy', $map)) {
            $this->resource->set($map['destroy'],
                (new Route([
                    'expression' => Router::mapPatterns("{$uri}(:id)"),
                    'function'   => [$class_name, $map['destroy']],
                    'method'     => 'delete',
                    'middleware' => Router::$group['middleware'] ?? [],
                ]))->name("{$class_name}.destroy")
            );
        }

        return $this;
    }

    /**
     * @return CollectionImmutable<string, Route>
     */
    public function get(): CollectionImmutable
    {
        return $this->resource->immutable();
    }

    /**
     * @param string[] $resource
     */
    public function only(array $resource): self
    {
        $this->resource->only($resource);

        return $this;
    }

    /**
     * @param string[] $resource
     */
    public function except(array $resource): self
    {
        $this->resource->except($resource);

        return $this;
    }
}
