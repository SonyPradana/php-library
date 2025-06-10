<?php

declare(strict_types=1);

namespace System\Router\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
final class Where
{
    public function __construct(
        /**
         * @var array<string, string>
         */
        public array $where,
    ) {
    }
}
