<?php

declare(strict_types=1);

namespace System\Template;

class PropertyPool
{
    /** @var Property[] */
    private $pools = [];

    public function name(string $name): Property
    {
        return $this->pools[] = new Property($name);
    }

    /**
     * @return Property[]
     */
    public function getPools(): array
    {
        return $this->pools;
    }
}
