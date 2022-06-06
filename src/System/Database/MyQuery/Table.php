<?php

namespace System\Database\MyQuery;

use System\Database\MyPDO;

class Table
{
    protected $PDO = null;
    protected $table_name;

    public function __construct(string $table_name, MyPDO $PDO = null)
    {
        $this->table_name = $table_name;
        $this->PDO        = $PDO ?? MyPDO::getInstance();
    }

    public function insert()
    {
        $newQuery = new Insert($this->table_name, $this->PDO);

        return $newQuery;
    }

    public function select(array $select_columns = ['*'])
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
      'SELECT
        `COLUMN_NAME`,
        `COLUMN_TYPE`,
        `CHARACTER_SET_NAME`,
        `COLLATION_NAME`,
        `IS_NULLABLE`,
        `ORDINAL_POSITION`,
        `COLUMN_KEY`
      FROM
        INFORMATION_SCHEMA.COLUMNS
      WHERE
        TABLE_SCHEMA = :dbs AND TABLE_NAME = :table'
    );
        $this->PDO->bind(':table', $this->table_name);
        $this->PDO->bind(':dbs', $this->PDO->configs()['database_name']);

        return $this->PDO->resultset() ?? [];
    }
}
