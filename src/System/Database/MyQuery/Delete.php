<?php

namespace System\Database\MyQuery;

use System\Database\MyPDO;
use System\Database\MyQuery\Traits\ConditionTrait;

class Delete extends Execute
{
    use ConditionTrait;

    public function __construct(string $table_name, MyPDO $PDO)
    {
        $this->_table = $table_name;
        $this->PDO    = $PDO;
    }

    public function __toString()
    {
        return $this->builder();
    }

    protected function builder(): string
    {
        $where = $this->getWhere();

        $this->_query = "DELETE FROM `$this->_table` $where";

        return $this->_query;
    }
}
