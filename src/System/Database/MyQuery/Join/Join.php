<?php

namespace System\Database\MyQuery\Join;

abstract class Join
{
  protected $_masterTable = '';
  protected $_masterColumn = '';
  protected $_tableName  = '';
  protected $_colomnName = '';
  protected $_stringJoin = '';
  protected $_compereColumn = array();

  public function __invoke(string $master_table)
  {
    $this->_masterTable = $master_table;
    return $this;
  }
  
  // setter

  public function tableName(string $value)
  {
    $this->_tableName = $value;
    return $this;
  }

  public function compare(string $main_column, string $compire_column)
  {
    $this->_compereColumn[] =
      array (
        $main_column, $compire_column
    );
    return $this;
  }

  // getter
  public function stringJoin()
  {
    // run function
    $this->joinBuilder();
    return $this->_stringJoin;
  }

  // main

  protected function joinBuilder()
  {
    $this->_stringJoin;
  }

  protected function splitJoin()
  {
    $on = [];
    foreach ($this->_compereColumn as $column) {
      $masterColumn = $column[0];
      $compireColumn = $column[1];

      $on[] = "$this->_masterTable.$masterColumn = $this->_tableName.$compireColumn";
    }
    return implode(" AND ", $on);
  }
}
