<?php

declare(strict_types=1);

namespace System\Router\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
final class Name
{
    public function __construct(
        public string $name,
    ) {
    }
}
