<?php

namespace System\Database\MyQuery;

use System\Database\MyPDO;

class Delete extends Execute implements ConditionInterface
{
  public function __construct(string $table_name, MyPDO $PDO = null)
  {
    $this->_table = $table_name;
    $this->PDO    = $PDO ?? new MyPDO();
  }

  public function __toString()
  {
    return $this->builder();
  }

  public function equal(string $bind, string $value)
  {
    $this->compare($bind, '=', $value, false);
    return $this;
  }

  public function like(string $bind, string $value)
  {
    $this->compare($bind, 'LIKE', $value, false);
    return $this;
  }

  public function where(string $where_condition, ?array $binder = null)
  {
    $this->_where[] = $where_condition;

    if ($binder != null) {
      $this->_binder = array_merge($this->_binder, $binder);
    }

    return $this;
  }

  public function between(string $column_name, string $val_1, string $val_2)
  {
    $this->where(
      "(`$this->_table`.`$column_name` BETWEEN :b_start AND :b_end)",
      array(
        [':b_start', $val_1],
        [':b_end', $val_2]
      )
    );
    return $this;
  }

  public function in(string $column_name, array $val)
  {
    $binds = [];
    $binder = [];
    foreach ($val as $key => $bind) {
      $binds[] = ":in_$key";
      $binder[] = [":in_$key", $bind];
    }
    $bindString = implode(', ', $binds);

    $this->where(
      "(`$this->_table`.`$column_name` IN ($bindString))",
      $binder
    );

    return $this;
  }

  protected function builder(): string
  {
    $where = $this->getWhere();

    $this->_query = "DELETE FROM `$this->_table` $where";

    return $this->_query;
  }

}
