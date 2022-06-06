<?php

namespace System\Database\MyQuery\Join;

class RightJoin extends AbstractJoin
{
    protected function joinBuilder(): string
    {
        $on = $this->splitJoin();

        return "RIGHT JOIN $this->_tableName ON $on";
    }
}
