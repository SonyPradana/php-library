<?php

declare(strict_types=1);

namespace System\Router\Attributes\Resource;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class Store extends ResourceMap
{
    public function __construct(string $resource)
    {
        $this->resource_map[$resource] = [
            'method' => 'post',
            'map'    => $resource,
        ];
    }
}
