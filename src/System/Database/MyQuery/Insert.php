<?php

declare(strict_types=1);

namespace System\Database\MyQuery;

use System\Database\MyPDO;

class Insert extends Execute
{
    private int $uniq_bind = 0;

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
        $this->_binds[] = Bind::set($bind, $value, $bind)->prefixBind(':bind_' . $this->uniq_bind . '_');

        $this->uniq_bind++;

        return $this;
    }

    protected function builder(): string
    {
        [$binds, ,$columns] = $this->bindsDestructur();

        $stringBinds  = implode(', ', $binds);
        $stringColumn = implode(', ', $columns);

        $this->_query = "INSERT INTO `$this->_table` ($stringColumn) VALUES ($stringBinds)";

        return $this->_query;
    }
}
