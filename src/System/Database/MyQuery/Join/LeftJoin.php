<?php

namespace System\Database\MyQuery\Join;

class LeftJoin extends Join
{
  public function __construct()
  {
    $this->_stringJoin = "";
  }

  protected function joinBuilder()
  {
    $on = $this->splitJoin();
    $this->_stringJoin =
      "LEFT JOIN
        $this->_tableName
      ON
        $on
      ";
  }
}
