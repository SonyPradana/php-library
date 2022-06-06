<?php

namespace System\View;

class Portal
{
    private array $meta_info;

    public function __construct(array $meta_info)
    {
        $this->meta_info = $meta_info;
    }

    public function __get($name)
    {
        return $this->meta_info[$name] ?? $name;
    }

    public function has($name): bool
    {
        return isset($name);
    }
}
