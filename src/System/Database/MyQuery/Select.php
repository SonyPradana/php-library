<?php

namespace System\Database\MyQuery;

use System\Database\MyPDO;
use System\Database\MyQuery;
use System\Database\MyQuery\Join\Join;

class Select extends Fetch implements ConditionInterface
{
  public function __construct(string $table_name, array $columns_name, MyPDO $PDO = null)
  {
    $this->_table = $table_name;
    $this->_column = $columns_name;
    $this->PDO = $PDO ?? new MyPDO();

    // defaul query
    if (count($this->_column) > 1) {
      $this->_column = array_map (
        fn($e) => "`$e`",
        $this->_column
      );
    }

    $column = implode(', ', $columns_name);
    $this->_query = "SELECT $column FROM `$this->_table`";
  }

  public function __toString()
  {
    return $this->builder();
  }

  /**
   * Membuat join table
   *  - inner join
   *  - left join
   *  - right join
   *  - full join
   * @param Join $join_table Configure type of join
   */
  public function join(Join $join_table)
  {
    // overide master table
    $join_table($this->_table);

    $this->_join = $join_table->stringJoin();
    return $this;
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

  // sort, order, grouping

  /**
   * Set data start for feact all data
   * @param int $limit_start limit start
   * @param int $limit_end limit end
   */
  public function limit(int $limit_start, int $limit_end)
  {
    $this->_limit_start = $limit_start;
    $this->_limit_end = $limit_end;
    return $this;
  }

  /**
   * Set data start for feact all data
   * @param int $val limit start default is 0
   */
  public function limitStart(int $val)
  {
    $this->_limit_start = $val;
    return $this;
  }

  /**
   * Set data end for feact all data
   * zero value meaning no data show
   * @param int $val limit start default
   */
  public function limitEnd(int $val)
  {
    $this->_limit_end = $val;
    return $this;
  }

  /**
   * Set sort column and order
   * column name must register
   */
  public function order(string $column_name, int $order_using = MyQuery::ORDER_ASC)
  {
    $order = $order_using == 0 ? 'ASC' : 'DESC';
    $this->_sort_order = "ORDER BY `$this->_table`.`$column_name` $order";
    return $this;
  }

  /**
   * Setter strict mode
   *
   * True = operator using AND,
   * False = operator using OR
   * @param bool $value True where statment operation using AND
   */
  public function strictMode(bool $value)
  {
    $this->_strict_mode = $value;
    return $this;
  }

  // query builder

  protected function builder(): string
  {
    $column = implode(', ', $this->_column);
    $where  = $this->getWhere();
    $limit = $this->_limit_start < 0 ? "LIMIT $this->_limit_end" : "LIMIT $this->_limit_start, $this->_limit_end";
    $limit = $this->_limit_end < 1 ? '' : $limit;
    $join = $this->_join;

    $this->_query = "SELECT $column FROM `$this->_table` $join$where $this->_sort_order $limit";
    return $this->_query;
  }
}
