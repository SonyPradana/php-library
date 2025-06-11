<?php

declare(strict_types=1);

namespace System\Router\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class Prefix
{
    public function __construct(
        public string $prefix,
    ) {
    }
}
