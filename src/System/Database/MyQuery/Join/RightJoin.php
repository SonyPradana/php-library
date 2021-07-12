<?php

namespace System\Database\MyQuery\Join;

class RightJoin extends Join
{
  protected function joinBuilder()
  {
    $on = $this->splitJoin();
    $this->_stringJoin =
      "RIGHT JOIN
        $this->_tableName
      ON
        $on
      ";
  }
}
