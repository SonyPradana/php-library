<?php

namespace System\Database\MyQuery\Join;

class LeftJoin extends AbstractJoin
{
  protected function joinBuilder(): string
  {
    $on = $this->splitJoin();
    return "LEFT JOIN $this->_tableName ON $on";
  }
}
