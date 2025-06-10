<?php

declare(strict_types=1);

namespace System\Router\Attributes\Resource;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class Update extends ResourceMap
{
    public function __construct(string $resource)
    {
        $this->resource_map[$resource] = [
            'method' => ['put', 'patch'],
            'map'    => $resource,
        ];
    }
}
