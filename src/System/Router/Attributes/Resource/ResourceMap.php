<?php

declare(strict_types=1);

namespace System\Router\Attributes\Resource;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class ResourceMap
{
    public function __construct(
        /** @var array<string, array<string, string>> */
        public array $resource_map,
    ) {
    }
}
