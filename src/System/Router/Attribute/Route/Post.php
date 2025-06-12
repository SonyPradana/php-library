<?php

declare(strict_types=1);

namespace System\Router\Attribute\Route;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class Post extends Route
{
    public function __construct(string $expression)
    {
        parent::__construct(['POST'], $expression);
    }
}
