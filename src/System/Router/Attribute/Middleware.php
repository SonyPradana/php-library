<?php

declare(strict_types=1);

namespace System\Router\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
final class Middleware
{
    public function __construct(
        /**
         * @var string[]
         */
        public array $middlware,
    ) {
    }
}
