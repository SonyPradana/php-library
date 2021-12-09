<?php

namespace System\Database\MyQuery\Join;

class InnerJoin extends AbstractJoin
{
  protected function joinBuilder(): string
  {
    $on = $this->splitJoin();
    return "INNER JOIN $this->_tableName ON $on";
  }
}
