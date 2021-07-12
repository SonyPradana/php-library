<?php

namespace System\Database\MyQuery;

use System\Database\MyPDO;
use System\Database\MyQuery\Insert;
use System\Database\MyQuery\Select;

class Table
{
  protected $PDO = null;
  protected $table_name;

  public function __construct(string $table_name, MyPDO $PDO = null)
  {
    $this->table_name = $table_name;
    $this->PDO = $PDO ?? new MyPDO();
  }

  public function insert()
  {
    $newQuery = new Insert($this->table_name, $this->PDO);

    return $newQuery;
  }

  public function select(array $select_columns = array('*'))
  {
    $newQuery = new Select($this->table_name, $select_columns, $this->PDO);

    return $newQuery;
  }

  public function update()
  {
    $newQuery = new Update($this->table_name, $this->PDO);

    return $newQuery;
  }

  public function delete()
  {
    $newQuery = new Delete($this->table_name, $this->PDO);

    return $newQuery;
  }

  public function info(): array
  {
    $this->PDO->query(
      "SELECT
        `COLUMN_NAME`,
        `COLUMN_TYPE`,
        `CHARACTER_SET_NAME`,
        `COLLATION_NAME`,
        `IS_NULLABLE`,
        `ORDINAL_POSITION`
      FROM
        INFORMATION_SCHEMA.COLUMNS
      WHERE
        TABLE_SCHEMA = :dbs AND TABLE_NAME = :table"
    );
    $this->PDO->bind(':table', $this->table_name);
    $this->PDO->bind(':dbs', DB_NAME);
    return $this->PDO->resultset() ?? array();
  }
}
