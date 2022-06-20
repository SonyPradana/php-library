<?php

declare(strict_types=1);

namespace System\Database\MyQuery;

use System\Database\MyPDO;
use System\Database\MyQuery\Traits\ConditionTrait;

class Update extends Execute
{
    use ConditionTrait;

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
     * Insert set value (single).
     *
     * @param string[] $values Array of bing and value
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
     * Insert set value (single).
     *
     * @param string $bind  Pdo bind
     * @param string $value Value of the bind
     *
     * @return self
     */
    public function value(string $bind, string $value)
    {
        $this->_binder[] = [$bind, $value, true];

        return $this;
    }

    protected function builder(): string
    {
        $where = $this->getWhere();

        $setArray = array_map(
            fn ($e, $c) => $c == true ? "`$e` = :val_$e" : null,
            array_column($this->_binder, 0),
            array_column($this->_binder, 2)
        );
        $setArray   = array_filter($setArray);  // remove empety items
        $setString  = implode(', ', $setArray); // concvert to string

        $this->_query = "UPDATE `$this->_table` SET $setString $where";

        return $this->_query;
    }
}
