<?php

namespace System\Template;

class PropertyPool
{
    private $pools = [];

    public function name(string $name)
    {
        return $this->pools[] = new Property($name);
    }

    public function getPools(): array
    {
        return $this->pools;
    }
}
