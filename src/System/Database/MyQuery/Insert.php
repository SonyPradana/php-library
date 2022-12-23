<?php

declare(strict_types=1);

namespace System\Database\MyQuery;

use System\Database\MyPDO;

class Insert extends Execute
{
    public function __construct(string $table_name, MyPDO $PDO)
    {
        $this->_table = $table_name;
        $this->PDO    = $PDO;
    }

    public function __toString()
    {
        return $this->builder();
    }

    /**
     *  Value query builder (key => value).
     *
     * @param array<string, string|int|bool|null> $values Insert values
     *
     * @return self
     */
    public function values($values)
    {
        foreach ($values as $key => $value) {
            $this->value($key, $value);
        }

        return $this;
    }

    /**
     * @param string|int|bool|null $value
     *
     * @return self
     */
    public function value(string $bind, $value)
    {
        $this->_binds[] = Bind::set($bind, $value, $bind)->prefixBind(':bind_');

        return $this;
    }

    public function raws(array $raws): self
    {
        foreach ($raws as $index => $values) {
            foreach ($values as $bind => $value) {
                $this->_binds[] = Bind::set($bind, $value, $bind)->prefixBind(':bind_' . $index . '_');
            }
        }

        return $this;
    }

    protected function builder(): string
    {
        [$binds, ,$columns] = $this->bindsDestructur();

        $strings_binds = [];
        /** @var array<int, array<int, string>> */
        $chunk         = array_chunk($binds, count($columns), true);
        foreach ($chunk as $group) {
            $strings_binds[] = '(' . implode(', ', $group) . ')';
        }

        $stringBinds  = implode(', ', $strings_binds);
        $stringColumn = implode(', ', $columns);

        $this->_query = "INSERT INTO `$this->_table` ($stringColumn) VALUES $stringBinds";

        return $this->_query;
    }
}
