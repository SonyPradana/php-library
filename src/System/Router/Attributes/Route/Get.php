<?php

declare(strict_types=1);

namespace System\Router\Attributes\Route;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class Get extends Route
{
    public function __construct(string $expression)
    {
        parent::__construct(['GET'], $expression);
    }
}
