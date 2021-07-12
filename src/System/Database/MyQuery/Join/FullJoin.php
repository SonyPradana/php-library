<?php

namespace System\Database\MyQuery\Join;

class FullJoin extends Join
{
  public function __construct()
  {
    $this->_stringJoin = "";
  }

  protected function joinBuilder()
  {
    $on = $this->splitJoin();
    $this->_stringJoin =
      "FULL OUTER JOIN
        $this->_tableName
      ON
        $on
      ";
  }
}
