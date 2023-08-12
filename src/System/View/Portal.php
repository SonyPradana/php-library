<?php

declare(strict_types=1);

namespace System\View;

class Portal
{
    /**
     * Item collection.
     *
     * @var array<string, mixed>
     */
    private array $items;

    /**
     * Set portal items.
     *
     * @param array<string, mixed> $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * Get property value.
     *
     * @param string $name Property name
     *
     * @return mixed Property value, null if not found     *
     */
    public function __get($name)
    {
        return $this->items[$name] ?? null;
    }

    /**
     * Check property has exists or not.
     *
     * @param string $name Property name
     *
     * @return bool True if property name exists
     */
    public function has($name): bool
    {
        return isset($this->items[$name]);
    }
}
