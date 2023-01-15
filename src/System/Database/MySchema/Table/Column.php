<?php

declare(strict_types=1);

namespace System\Database\MySchema\Table;

class Column
{
    protected $query;

    public function __toString()
    {
        return $this->query;
    }

    public function raw(string $query): self
    {
        $this->query = $query;

        return $this;
    }
}
