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

        $this->PDO->query($this->_query);
        foreach ($this->_binder as $bind) {
            $this->PDO->bind($bind[0], $bind[1]);
        }
        $result = $this->PDO->single();

        return $result === false ? [] : $this->PDO->single();
    }

    /** @return array<string|int, mixed>|false */
    public function all()
    {
        $this->builder();

        $this->PDO->query($this->_query);
        foreach ($this->_binder as $bind) {
            $this->PDO->bind($bind[0], $bind[1]);
        }

        return $this->PDO->resultset();
    }
}
