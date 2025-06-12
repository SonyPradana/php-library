<?php

declare(strict_types=1);

namespace System\Router\Attribute\Route;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class Head extends Route
{
    public function __construct(string $expression)
    {
        parent::__construct(['HEAD'], $expression);
    }
}
