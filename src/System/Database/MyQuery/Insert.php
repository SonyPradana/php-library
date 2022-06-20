<?php

declare(strict_types=1);

namespace System\Database\MyQuery;

use System\Database\MyPDO;

class Insert extends Execute
{
    public function __construct(string $table_name, MyPDO $PDO = null)
    {
        $this->_table = $table_name;
        $this->PDO    = $PDO ?? MyPDO::getInstance();
    }

    public function __toString()
    {
        return $this->builder();
    }

    /**
     *  Value query builder (key => value).
     *
     * @param array<string, string> $values Insert values
     *
     * @return self
     */
    public function values(array $values)
    {
        foreach ($values as $key => $value) {
            $this->_binder[] = [$key, $value, true];
        }

        return $this;
    }

    /**
     * @return self
     */
    public function value(string $bind, string $value)
    {
        $this->_binder[] = [$bind, $value, true];

        return $this;
    }

    protected function builder(): string
    {
        $arraycolumns = array_column($this->_binder, 0);
        $arrayBinds   = array_map(
            fn ($e) => ":val_$e",
            array_column($this->_binder, 0)
        );
        $arraycolumns = array_filter($arraycolumns);
        $arrayBinds   = array_filter($arrayBinds);

        $stringColumn = implode(', ', $arraycolumns);
        $stringBinds  = implode(', ', $arrayBinds);

        $this->_query = "INSERT INTO `$this->_table` ($stringColumn) VALUES ($stringBinds)";

        return $this->_query;
    }
}
