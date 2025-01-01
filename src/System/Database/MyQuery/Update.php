<?php

declare(strict_types=1);

namespace System\Database\MyQuery;

use System\Database\MyPDO;
use System\Database\MyQuery\Traits\ConditionTrait;
use System\Database\MyQuery\Traits\SubQueryTrait;

class Update extends Execute
{
    use ConditionTrait;
    use SubQueryTrait;

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
     * Insert set value (single).
     *
     * @param array<string, string|int|bool|null> $values Array of bing and value
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
     * Insert set value (single).
     *
     * @param string               $bind  Pdo bind
     * @param string|int|bool|null $value Value of the bind
     *
     * @return self
     */
    public function value(string $bind, $value)
    {
        $this->_binds[] = Bind::set($bind, $value, $bind)->prefixBind(':bind_');

        return $this;
    }

    protected function builder(): string
    {
        $where = $this->getWhere();

        $setter = [];
        foreach ($this->_binds as $bind) {
            if ($bind->hasColumName()) {
                $setter[] = $bind->getColumnName() . ' = ' . $bind->getBind();
            }
        }

        // $binds       = array_filter($setter);
        $set_string  = implode(', ', $setter);

        $this->_query = "UPDATE $this->_table SET $set_string $where";

        return $this->_query;
    }
}
