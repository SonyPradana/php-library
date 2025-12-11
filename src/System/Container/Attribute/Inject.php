<?php

declare(strict_types=1);

namespace System\Container\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
final class Inject
{
    public function __construct(
        /** @var string|array<string, string> */
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
