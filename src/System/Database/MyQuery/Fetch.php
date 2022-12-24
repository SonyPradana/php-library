<?php

declare(strict_types=1);

namespace System\Database\MyQuery;

use System\Collection\Collection;

abstract class Fetch extends Query
{
    public function get(): ?Collection
    {
        return new Collection($this->all());
    }

    /**
     * @return string[]|mixed
     */
    public function single()
    {
        $this->builder();
        $result = $this->binding()->single();

        return $result === false ? [] : $result;
    }

    /** @return array<string|int, mixed>|false */
    public function all()
    {
        $this->builder();

        return $this->binding()->resultset();
    }
}
