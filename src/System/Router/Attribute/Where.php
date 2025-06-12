<?php

declare(strict_types=1);

namespace System\Router\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class Where
{
    public function __construct(
        /**
         * @var array<string, string>
         */
        public array $pattern,
    ) {
    }
}
