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
        foreach ($this->_binds as $bind) {
            if (!$bind->hasBind()) {
                $this->PDO->bind($bind->getBind(), $bind->getValue());
            }
        }
        $result = $this->PDO->single();

        return $result === false ? [] : $this->PDO->single();
    }

    /** @return array<string|int, mixed>|false */
    public function all()
    {
        $this->builder();

        $this->PDO->query($this->_query);
        foreach ($this->_binds as $bind) {
            if (!$bind->hasBind()) {
                $this->PDO->bind($bind->getBind(), $bind->getValue());
            }
        }

        return $this->PDO->resultset();
    }
}
