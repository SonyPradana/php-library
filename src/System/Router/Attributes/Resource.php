<?php

declare(strict_types=1);

namespace System\Router\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class Resource
{
    public function __construct(
        public string $expression,
    ) {
    }
}
