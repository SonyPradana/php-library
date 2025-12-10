<?php

declare(strict_types=1);

namespace System\Container\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
final class Inject
{
    public function __construct(
        private string|array $name = [],
    ) {
    }

    /**
     * @return string|array<string, string>
     */
    public function getName(): string|array
    {
        return $this->name;
    }
}
