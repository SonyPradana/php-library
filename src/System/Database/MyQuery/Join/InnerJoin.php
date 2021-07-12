<?php

namespace System\Database\MyQuery\Join;

class InnerJoin extends Join
{
  protected function joinBuilder()
  {
    $on = $this->splitJoin();
    $this->_stringJoin =
      "INNER JOIN
        $this->_tableName
      ON
        $on
      ";
  }
}
